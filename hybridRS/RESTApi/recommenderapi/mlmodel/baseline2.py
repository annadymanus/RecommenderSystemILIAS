#Collaborative Filtering baseline
import pandas as pd
import numpy as np
from surprise import Reader
from surprise import KNNWithMeans
from surprise import Dataset
from dataRetriever import get_all_recommendations_for_crs, section_to_identifier, identifier_to_section
from collections import defaultdict
import json

crs_id=132
all_recs = get_all_recommendations_for_crs(crs_id)
all_recs = [(rec[0], section_to_identifier(rec[1], rec[2])) for rec in all_recs]
all_valid_items = list(set([rec[1] for rec in all_recs]))

max_count = 0 
count_dict = defaultdict(dict)
for rec in all_recs:
    if rec[1] in count_dict[rec[0]]:
        count_dict[rec[0]][rec[1]] += 1
        if count_dict[rec[0]][rec[1]] > max_count:
            max_count = count_dict[rec[0]][rec[1]]
    else:
        count_dict[rec[0]][rec[1]] = 1  

#normalize count dict
for user in count_dict:
    for item in count_dict[user]:
        count_dict[user][item] = count_dict[user][item]/max_count

ratings_dict = {
    "item": [],
    "user": [],
    "rating": [],    
}

for user in count_dict:
    for item in count_dict[user]:
        ratings_dict["item"].append(item)
        ratings_dict["user"].append(user)
        ratings_dict["rating"].append(count_dict[user][item])

df = pd.DataFrame(ratings_dict)
reader = Reader(rating_scale=(0,1))

#Loads Pandas dataframe
data = Dataset.load_from_df(df[["user", "item", "rating"]], reader)

for based in ["user", "item"]:
    # To use user-based cosine similarity
    sim_options = {
        "name": "cosine",
        "user_based": True if based=="user" else False, #for item-based CF
    }
    algo = KNNWithMeans(sim_options=sim_options)
    trainset = data.build_full_trainset()
    algo.fit(trainset)

    all_preds = {}
    for user in count_dict:
        user_preds = []
        for item in all_valid_items:
            if item in count_dict[user]:
                continue
            pred = algo.predict(user, item)
            user_preds.append((item, pred.est))
        user_preds = [[int(x) for x in identifier_to_section(pred[0])] for pred in sorted(user_preds, key=lambda x: x[1], reverse=True)]
        all_preds[user] = user_preds

    json.dump(all_preds, open(f"baseline_{based}.json","w"))