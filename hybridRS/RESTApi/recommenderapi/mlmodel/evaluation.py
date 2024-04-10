import os
from dataRetriever import Database

# baseline model based on the interests of students
# The idea: the more tags section and material type gets, the more interested it will be


def get_top_sections_per_material_type():
    # Initialize the Database object with your connection details
    db = Database(user=os.getenv("ILIAS_DB_USER"), password=os.getenv("ILIAS_DB_PASS"), host=os.getenv("ILIAS_DB_HOST"), port=int(os.getenv("ILIAS_DB_PORT")), database=os.getenv("ILIAS_DB_NAME"))

    # Query to find the sections and material types with the most tags assigned
    query = """
            SELECT section_id, material_type, COUNT(*) AS tag_count
            FROM ui_uihk_recsys_t_p_s
            GROUP BY section_id, material_type
            ORDER BY tag_count DESC
            LIMIT 5
            """

    db.execute(query)
    
    results = db.fetchall()  # Fetch all rows returned by the query

    db.connection.close()

    return results

    # TO DO: think about providing not sections and material type, but maybe tags? Or their names?


#   baseline model based on the frequency of tags
# The idea: take the most frequently used tags within a course

def get_top_tags_for_crs_id(crs_id):

    db = Database(user=os.getenv("ILIAS_DB_USER"), password=os.getenv("ILIAS_DB_PASS"), host=os.getenv("ILIAS_DB_HOST"), port=int(os.getenv("ILIAS_DB_PORT")), database=os.getenv("ILIAS_DB_NAME"))

    query = """
            SELECT tag_name, tag_count
            FROM ui_uihk_recsys_tags
            WHERE crs_id = %s
            ORDER BY tag_count DESC
            LIMIT 5
            """
    
    db.execute(query, (crs_id,))
    results = db.fetchall()  # Fetch all rows returned by the query

    db.connection.close()

    return results



############################################################################################################
# CREATE SYNTHETIC DATA FOR TRAINING AND TESTING. NEEDS INITIALIZED USER BEHAVIOUR TABLES ui_uihk_recsys_u_q, ui_uihk_recsys_u_c 
# CONTAINING ONLY SAMPLES FOR BELOW USER PROFILES
############################################################################################################
import csv
import torch
import dataRetriever
from dataRetriever import get_past_recommendations, DB, section_to_identifier, identifier_to_section, get_past_queries
import time
from collections import defaultdict
from sklearn.naive_bayes import BernoulliNB
import numpy as np
from model import ModelHandler
import json

class DataGenerator:
    def __init__(self, user_profile_file, crs_id, sample_timespan, safe_mode=False) -> None:
        self.crs_id = crs_id
        self.safe_mode = safe_mode
        self.user_profiles = {}
        with open(user_profile_file, newline='') as csvfile:
            reader = csv.reader(csvfile, delimiter=",")
            for row in reader:
                self.user_profiles[int(row[0])] = [int(entry) for entry in row[1:]]
        self.idx_to_usr_id = [usr_id for usr_id in self.user_profiles.keys()]
        self.user_profile_matrix = torch.tensor([self.user_profiles[usr_id] for usr_id in self.idx_to_usr_id])
        
        self.existing_queries = defaultdict(list)
        self.queries = defaultdict(list)
        self.existing_recommendations = defaultdict(list)
        self.recommendations = defaultdict(list)
        self.sample_timespan = sample_timespan
        
        for usr_id in self.idx_to_usr_id:
            queries, recs = self.time_normalize_existing_user_interaction(usr_id, sample_timespan[0], sample_timespan[1])
            if len(queries + recs) > 0:
                self.existing_queries[usr_id] = queries
                self.queries[usr_id] = queries
                self.existing_recommendations[usr_id] = recs
                self.recommendations[usr_id] = recs

    def time_normalize_existing_user_interaction(self, usr_id, min_timestamp, max_timestamp):
        queries = get_past_queries(usr_id, self.crs_id, timestamp=999999999999999999999)
        queries = [(int(query[0]), int(query[1]), int(query[2])) for query in queries]
        self.usr_min_timestamp = min([query[2] for query in queries])
        recommendations = get_past_recommendations(usr_id, self.crs_id, timestamp=999999999999999999999)
        recommendations = [(int(rec[0]), int(rec[1]), int(rec[2])) for rec in recommendations]
        self.usr_max_timestamp = max([recommendation[2] for recommendation in recommendations])
        #normalize to min_timestamp and max_timestamp
        new_queries = []
        query_original_to_new_timestamp= {}
        for query in queries:
            relative = (query[2] - self.usr_min_timestamp) / (self.usr_max_timestamp - self.usr_min_timestamp)
            new_timestamp = int(min_timestamp + relative * (max_timestamp - min_timestamp))
            #write back to db (overwrite timestamp)
            query_original_to_new_timestamp[query[2]] = new_timestamp
            db_query = f"""
                    UPDATE ui_uihk_recsys_u_q
                    SET timestamp = {new_timestamp}
                    WHERE usr_id = {usr_id} AND crs_id = {self.crs_id} AND material_id = {query[0]} AND material_type = {query[1]} AND timestamp = {query[2]};
                    """
            if not self.safe_mode:
                DB.write(db_query)
            new_queries.append((query[0], query[1], new_timestamp))
        
        query_original_to_new_timestamp = sorted(query_original_to_new_timestamp.items(), key=lambda x: -x[0])
        new_recommendations = []
        for recommendation in recommendations:
            new_timestamp = None
            for query_timestamp, new_query_timestamp in query_original_to_new_timestamp:
                if recommendation[2] - query_timestamp > 0:
                    new_timestamp = new_query_timestamp + int(torch.rand(1) * 60*60*2)
                    break
            if new_timestamp == None:
                relative = (recommendation[2] - self.usr_min_timestamp) / (self.usr_max_timestamp - self.usr_min_timestamp)
                new_timestamp = int(min_timestamp + relative * (max_timestamp - min_timestamp))
            #write back to db (overwrite timestamp)
            db_query = f"""
                    UPDATE ui_uihk_recsys_u_c
                    SET timestamp = {new_timestamp}
                    WHERE usr_id = {usr_id} AND crs_id = {self.crs_id} AND material_id = {recommendation[0]} AND material_type = {recommendation[1]} AND timestamp = {recommendation[2]};
                    """
                    
            if not self.safe_mode:
                DB.write(db_query)
            new_recommendations.append((recommendation[0], recommendation[1], new_timestamp))

        return new_queries, new_recommendations
    
    def sample_new_profile(self, new_usr_id):
        probs = self.user_profile_matrix.sum(dim=0) / len(self.user_profile_matrix)
        #add noise
        probs = torch.clamp(probs + torch.tensor([np.random.normal(0, 0.05, 1) for _ in range(len(probs))]).squeeze(),min=0,max=1)
        new_profile = [1 if torch.rand(1) < prob else 0 for prob in probs]
        self.user_profiles[new_usr_id] = new_profile
        self.idx_to_usr_id.append(new_usr_id)
        self.user_profile_matrix = torch.tensor([self.user_profiles[usr_id] for usr_id in self.idx_to_usr_id])
    
    def write_sampled_data_to_db(self):
        #take difference queries - existing queries
        for usr_id, queries in self.queries.items():
            if usr_id in self.existing_queries:
                new_queries = [query for query in queries if query not in self.existing_queries[usr_id]]
            else:
                new_queries = queries
            for query in new_queries:
                db_query = f"""
                    INSERT INTO ui_uihk_recsys_u_q (usr_id, crs_id, material_id, material_type, timestamp)
                    VALUES ({usr_id}, {self.crs_id}, {query[0]}, {query[1]}, {query[2]});
                    """
                if not self.safe_mode:
                    DB.write(db_query)
                    
        
        for usr_id, recs in self.recommendations.items():
            if usr_id in self.existing_recommendations:
                new_recs = [rec for rec in recs if rec not in self.existing_recommendations[usr_id]]
            else:
                new_recs = recs
            for rec in new_recs:
                db_query = f"""
                    INSERT INTO ui_uihk_recsys_u_c (usr_id, crs_id, material_id, material_type, timestamp)
                    VALUES ({usr_id}, {self.crs_id}, {rec[0]}, {rec[1]}, {rec[2]});
                    """
                if not self.safe_mode:
                    DB.write(db_query)
                
    def train_test_split(self, test_ratio=0.3):
        
        usr_queries = defaultdict(list)
        for usr_id, queries in self.queries.items():
            for query in queries:
                full_query = (usr_id, self.crs_id, *query)
                usr_queries[usr_id].append(full_query)
            usr_queries[usr_id] = sorted(usr_queries[usr_id], key=lambda x: -x[-1])
        
        test_usr_queries = defaultdict(list)
        usr_cutoff_timestamps = {}
        for usr_id, test_qs in usr_queries.items():
            usr_test_set = test_qs[:int(len(test_qs) * test_ratio)]
            test_usr_queries[usr_id].extend(usr_test_set)
            usr_cutoff_timestamps[usr_id] = usr_test_set[-1][-1]
            
            
        #remove test queries from self.queries
        for usr_id, test_queries in test_usr_queries.items():
            for query in test_queries:
                self.queries[usr_id].remove(query[2:])
                
        test_recs_with_queries = []
        for usr_id in self.idx_to_usr_id:
            for rec in self.recommendations[usr_id]:
                if rec[2] > usr_cutoff_timestamps[usr_id]:
                    full_recommendation = (usr_id, self.crs_id, *rec)
                    self.recommendations[usr_id].remove(rec)
                    rec_queries = []
                    for query in test_usr_queries[usr_id]:                        
                        if query[-1] < rec[2] < query[-1] + 60*60*6:
                            rec_queries.append(query)
                    if len(rec_queries)>0:
                        test_recs_with_queries.append({"recommendation": full_recommendation, "query":rec_queries})

        self.test_recs_with_queries = test_recs_with_queries
        json.dump(test_recs_with_queries, open("test_recs_with_queries.json", "w"))

    
                
    def sample_new_observations(self, user_ids, num_queries, num_items_per_query, num_recs_per_query, timestamp_range):
        #Generate query dataset
        y = []
        x = []
        all_user_queries = defaultdict(list)
        for i, usr_id in enumerate(self.idx_to_usr_id):
            queries = self.queries[usr_id]
            for query in queries:
                q_id = section_to_identifier(query[0], query[1])
                all_user_queries[usr_id].append(q_id)
                x.append(self.user_profiles[usr_id])
                y.append(q_id)
        #q_id_to_idx = {q_id: idx for idx, q_id in enumerate(set(y))}
        #y = [q_id_to_idx[rec_id] for rec_id in y]
        
        #Fit BernoulliNB
        clf = BernoulliNB()
        clf.fit(x, y)
        
        
        new_sampled_queries = defaultdict(list)
        #Sample new recommendations for user_ids
        for usr_id in user_ids:
            x_new = self.user_profiles[usr_id]
            joint_log_probs = clf.predict_joint_log_proba([x_new])
            
            # Just in case sklearn chooses different class indices
            joint_probs = torch.exp(torch.tensor(joint_log_probs[0]))
            joint_probs = joint_probs / joint_probs.sum()
            #add gaussian noise
            joint_probs = torch.clamp(joint_probs + np.random.normal(0, 0.1, len(joint_probs)), min=0)

            joint_probs = {clf.classes_[j] : joint_probs[j] for j in range(len(clf.classes_))}
            
            q_probs = {key:v.item() for key, v in joint_probs.items()}
                                  
            #Penalize already recommended sections
            for q_id in all_user_queries[usr_id]:
                q_probs[q_id] *= 0.5
            
            q_probs_labels = []
            q_probs_values = []
            for key, value in q_probs.items():
                q_probs_labels.append(key)
                q_probs_values.append(value)
            for n in range(num_queries):
                q_idcs = torch.multinomial(torch.tensor(q_probs_values), num_items_per_query, replacement=False)#replacement=len(q_probs_values) < num_items_per_query)
                q_idcs = [q_probs_labels[i.item()] for i in q_idcs]
                sampled_timestamp = int(timestamp_range[0] + torch.rand(1) * (timestamp_range[1] - timestamp_range[0]))
                new_sampled_queries[usr_id].extend([(*[int(x) for x in identifier_to_section(q_id)], sampled_timestamp) for q_id in q_idcs])
        
        #Get all queried section_ids to create encodings
        section_ids = []
        for usr_id in self.idx_to_usr_id:
            section_ids.extend([section_to_identifier(query[0], query[1]) for query in self.queries[usr_id]])
        section_ids = list(set(section_ids))
        
        #Generate recommendation dataset
        y = []
        x = []
        all_user_recs = defaultdict(list)
        for i, usr_id in enumerate(self.idx_to_usr_id):
            recs = self.recommendations[usr_id]
            for rec in recs:
                rec_id = section_to_identifier(rec[0], rec[1])                
                all_user_recs[usr_id].append(rec_id)
                rec_timestamp = rec[2]
                
                #Get prior queries
                prior_query_timestamp = min([rec_timestamp-x[2] if x[2] < rec_timestamp else float("inf") for x in self.queries[usr_id]])
                prior_queries = [x for x in self.queries[usr_id] if x[2] == prior_query_timestamp]
                #Encode them
                prio_query_encoding = [0]*len(section_ids)
                for prior_query in prior_queries:
                    prio_query_encoding[section_ids.index(section_to_identifier(prior_query[0], prior_query[1]))] = 1

                combined_x = self.user_profiles[usr_id] + prio_query_encoding
                x.append(combined_x)
                y.append(rec_id)

        
        #Fit BernoulliNB
        clf = BernoulliNB()
        clf.fit(x, y)
        
        new_sampled_recommendations = defaultdict(list)
        #Sample new recommendations for user_ids and newly sampled queries
        for usr_id in user_ids:
            for query in new_sampled_queries[usr_id]:
                x_new = self.user_profiles[usr_id]
                query_encoding = [0]*len(section_ids)
                query_encoding[section_ids.index(section_to_identifier(query[0], query[1]))] = 1
                x_combined = x_new + query_encoding
                joint_log_probs = clf.predict_joint_log_proba([x_combined])
                joint_probs = torch.exp(torch.tensor(joint_log_probs[0]))
                joint_probs = joint_probs / joint_probs.sum()
                #add gaussian noise
                joint_probs = torch.clamp(joint_probs + np.random.normal(0, 0.1, len(joint_probs)),min=0)
                joint_probs = {clf.classes_[j] : joint_probs[j] for j in range(len(clf.classes_))}

                #Sample num_recs recommendations
                rec_probs = {key:v.item() for key, v in joint_probs.items()}
                
                #Penalize already recommended sections
                for rec_id in all_user_recs[usr_id]:
                    rec_probs[rec_id] *= 0.5
                
                rec_probs_labels = []
                rec_probs_values = []
                for key, value in rec_probs.items():
                    rec_probs_labels.append(key)
                    rec_probs_values.append(value)
                rec_idcs = torch.multinomial(torch.tensor(rec_probs_values), num_recs_per_query, replacement=False)#len(rec_probs_values) < num_recs_per_query)
                rec_idcs = [rec_probs_labels[i.item()] for i in rec_idcs]                
                sampled_timestamps = [query[2] + int(torch.rand(1) * 60*60) for rec_id in rec_idcs]
                new_sampled_recommendations[usr_id].extend([(*[int(x) for x in identifier_to_section(rec_id)], sampled_timestamp) for sampled_timestamp, rec_id in zip(sampled_timestamps, rec_idcs)])
        
        # Add new queries and recommendations
        for usr, queries in new_sampled_queries.items():
            self.queries[usr].extend(queries)
        for usr, recs in new_sampled_recommendations.items():
            self.recommendations[usr].extend(recs)

class Evaluator:
    def __init__(self, test_recs_with_queries) -> None:
        self.test_recs_with_queries = test_recs_with_queries
    
    def test_ml_model(self):
        assert hasattr(self, "test_recs_with_queries"), "Please run train_test_split first"
        model = ModelHandler(encoder_types=["tag", "recquery", "pastquery", "pastclicked"],
                             tags=dataRetriever.get_all_tags(),
                             items=dataRetriever.get_all_item_identifiers(),
                             crs_id=132,
                             load_checkpoint=False)
        model.train()
        
        all_gold_recommendations = defaultdict(list)
        all_pred_recommendations = defaultdict(list)
        per_query_gold_recommendations = defaultdict(list)
        per_query_pred_recommendations = defaultdict(list)
        for item in self.test_recs_with_queries:
            rec = item["recommendation"]
            usr_id = rec[0]
            all_gold_recommendations[usr_id].append((rec[2], rec[3]))
            query = item["query"]
            sec_ids = [q[2] for q in query]
            mat_types = [q[3] for q in query]
            result_dict = model(query[0][0], query[0][1], sec_ids, mat_types, query[0][-1])
            predictions = sorted(result_dict["predictions"], key=lambda x: -x["score"])
            query_preds = []
            for i, prediction in enumerate(predictions):
                pred = (int(prediction["section_id"]), int(prediction["material_type"]), float(prediction["score"]))
                all_pred_recommendations[usr_id].append(pred)
                query_preds.append(pred)
            per_query_pred_recommendations[tuple(tuple(q) for q in query)].extend(query_preds)
            per_query_gold_recommendations[tuple(tuple(q) for q in query)].append((rec[2], rec[3],))
        
        for query, recs in per_query_pred_recommendations.items():
            recs = sorted(recs, key=lambda x: -x[2])
            per_query_pred_recommendations[query] = [r[:2] for r in recs]
        
        for usr_id in all_pred_recommendations:
            by_item = defaultdict(float)
            for rec in all_pred_recommendations[usr_id]:
                by_item[rec[:2]] += rec[2]
            all_pred_recommendations[usr_id] = [x[0] for x in sorted(by_item.items(), key=lambda x: x[1], reverse=True)]
        return all_gold_recommendations, all_pred_recommendations, per_query_gold_recommendations, per_query_pred_recommendations
    
    def compute_mrr(self, gold, prec):
        mrr = []
        for usr_id in gold:
            if len(gold[usr_id]) > 0:
                try:                    
                    usr_preds = [tuple(x) for x in prec[usr_id]]
                    usr_golds = [tuple(x) for x in gold[usr_id]]
                except KeyError:
                    u_id = usr_id[0]
                    while str(u_id) not in prec:
                        u_id = u_id[0]
                    usr_preds = [tuple(x) for x in prec[str(u_id)]]
                    usr_golds = [tuple(x) for x in gold[usr_id]]
                found = False
                for i, pred in enumerate(usr_preds):
                    if pred in usr_golds:
                        mrr.append(1/(i+1))
                        found=True
                        break
                if not found:
                    mrr.append(0)
        mrr = np.mean(mrr)
        return mrr
    
    def compute_precision_at_k(self, gold, pred, k):
        precision_at_k = []
        for usr_id in gold:
            if len(gold[usr_id]) > 0:
                usr_preds = [tuple(x) for x in pred[usr_id]]
                usr_golds = [tuple(x) for x in gold[usr_id]]
                precision_at_k.append(len(set(usr_golds) & set(usr_preds[:k])) / k)
        precision_at_k = np.mean(precision_at_k)
        return precision_at_k

    def compute_mean_average_precision(self, gold, pred, at_k=10):
        average_precisions = []
        for usr_id in gold:
            if len(gold[usr_id]) > 0:
                usr_preds = [tuple(x) for x in pred[usr_id]]
                usr_golds = [tuple(x) for x in gold[usr_id]]
                ap = 0
                relevance_count = 0
                for k in range(1, min(at_k+1, len(usr_preds))):
                    prec = len(set(usr_golds) & set(usr_preds[:k])) / k
                    relevance = 1 if usr_preds[k-1] in usr_golds else 0
                    relevance_count += relevance
                    ap += prec * relevance
                if relevance_count == 0:
                    average_precisions.append(0)
                else:
                    average_precisions.append(ap/relevance_count)
        mean_average_precision = np.mean(average_precisions)
        
        return mean_average_precision

    

if True:
    dg = DataGenerator("seed_data.txt",132,(0, 60*60*24*60), safe_mode=False)
    print("instatiated DG")
    for i in range(10):
        dg.sample_new_profile(10000+i)
    for i in range(2):
        dg.sample_new_observations(dg.idx_to_usr_id, 2, 2, 5, (0, 60*60*24*60))


    dg.write_sampled_data_to_db() 
    dg.train_test_split()
    exit()
eval = Evaluator(json.load(open("test_recs_with_queries.json")))
gold, pred, per_query_gold, per_query_pred = eval.test_ml_model()

print("Query MRR")
mrr = eval.compute_mrr(per_query_gold,per_query_pred)
print("ML", mrr)
mrr = eval.compute_mrr(per_query_gold, json.load(open("baseline_user.json")))
print("User", mrr)
mrr = eval.compute_mrr(per_query_gold, json.load(open("baseline_item.json")))
print("Item", mrr)
mrr = eval.compute_mrr(per_query_gold, json.load(open("baseline_collab.json")))
print("Collab", mrr)


json.dump(pred, open("ml_pred_recs.json", "w"))
json.dump(gold, open("gold_recs.json", "w"))
print()
print("Precisions")
for i in [1,3,5]:
    print("At", i)
    p = eval.compute_mean_average_precision(json.load(open("gold_recs.json")), json.load(open("ml_pred_recs.json")), i)
    print("ML", p)
    p = eval.compute_mean_average_precision(json.load(open("gold_recs.json")), json.load(open("baseline_user.json")), i)
    print("User", p)
    p = eval.compute_mean_average_precision(json.load(open("gold_recs.json")), json.load(open("baseline_item.json")), i)
    print("Item", p)
    p = eval.compute_mean_average_precision(json.load(open("gold_recs.json")), json.load(open("baseline_collab.json")), i)
    print("Collab", p)
    print()