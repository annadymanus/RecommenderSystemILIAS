import mariadb
import os
import random

class Database:
    def __init__(self, user, password, host, port, database):
        self.user = user
        self.password = password
        self.host = host
        self.port = port
        self.database = database
        self.connection = mariadb.connect(user=self.user, password=self.password, host=self.host, port=self.port, database=self.database)
        self.cursor = self.connection.cursor()
         
    def fetchall(self, query):
        self.cursor.execute(query)
        return self.cursor.fetchall()
    
    def write(self, query):
        self.cursor.execute(query)
        self.connection.commit()


DB = Database(user=os.getenv("ILIAS_DB_USER"), password=os.getenv("ILIAS_DB_PASS"), host=os.getenv("ILIAS_DB_HOST"), port=int(os.getenv("ILIAS_DB_PORT")), database=os.getenv("ILIAS_DB_NAME"))
FIRST_TIMESTAMP = None
TARGET_RECOMMENDATION_LIMIT = 24*60*60 # 24 hours
MIN_IMPORTANCE = 0.05

def get_target_recommendation(usr_id, crs_id, timestamp, time_limit):
    """
    Returns all entries (material_type, material_id, timestamp) from ui_uihk_recsys_u_c where the user_id is the target user and the timestamp is within the time_limit.
    """
    
    #return DB.fetchall("SELECT material_id, material_type, timestamp FROM ui_uihk_recsys_u_c WHERE usr_id = " + str(usr_id) 
    #                   + " AND crs_id = " + str(crs_id) 
    #                   + " AND timestamp >= " + str(timestamp) 
    #                   + " AND timestamp <= " + str(timestamp+time_limit) + ";")
    
    #rewrite above query but only select material_id material_type tuples that exist in the ui_uihk_recsys_t_p_s table
    return DB.fetchall("SELECT material_id, material_type, timestamp FROM ui_uihk_recsys_u_c WHERE usr_id = " + str(usr_id)
                       + " AND crs_id = " + str(crs_id)
                       + " AND timestamp >= " + str(timestamp)
                       + " AND timestamp <= " + str(timestamp+time_limit)
                       + " AND (material_id, material_type) IN (SELECT material_id, material_type FROM ui_uihk_recsys_t_p_s);")

def get_tags_for_materials(section_ids, material_types):
    section_id_type_tuples = zip(section_ids, material_types)
    conjunctions = [f"(section_id = {elem[0]} AND material_type = {elem[1]})" for elem in section_id_type_tuples]
    dnf = " OR ".join(conjunctions)
    tag_ids_query = """
        SELECT tag_id 
        FROM ui_uihk_recsys_t_p_s 
        WHERE {} 
    """.format(dnf)
    return DB.fetchall(tag_ids_query)

def get_past_queries(usr_id, crs_id, timestamp):
    past_queries_query = """
        SELECT material_id, material_type, timestamp 
        FROM ui_uihk_recsys_u_q
        WHERE usr_id = {}
        AND timestamp < {}
        AND (material_id, material_type) IN (SELECT material_id, material_type FROM ui_uihk_recsys_t_p_s);
    """.format(usr_id, timestamp)
    return DB.fetchall(past_queries_query)

def get_all_past_queries():
    past_queries_query = """
        SELECT usr_id, crs_id, material_id, material_type, timestamp 
        FROM ui_uihk_recsys_u_q
        WHERE (material_id, material_type) IN (SELECT material_id, material_type FROM ui_uihk_recsys_t_p_s);
    """
    return DB.fetchall(past_queries_query)

def get_past_recommendations(usr_id, crs_id, timestamp):
    past_recommendations_query = """
        SELECT material_id, material_type, timestamp 
        FROM ui_uihk_recsys_u_c
        WHERE usr_id = {}
        AND timestamp < {}
        AND (material_id, material_type) IN (SELECT material_id, material_type FROM ui_uihk_recsys_t_p_s);
    """.format(usr_id, timestamp)
    return DB.fetchall(past_recommendations_query)

def get_all_recommendations_for_crs(crs_id):
    all_recommendations_for_crs_query = """
        SELECT usr_id, material_id, material_type, timestamp
        FROM ui_uihk_recsys_u_c
        WHERE crs_id = {}
        AND (material_id, material_type) IN (SELECT material_id, material_type FROM ui_uihk_recsys_t_p_s);
    """.format(crs_id)
    return DB.fetchall(all_recommendations_for_crs_query)

def get_all_tags():
    all_tags_query = """
        SELECT tag_id
        FROM ui_uihk_recsys_tags
    """
    return [tag[0] for tag in DB.fetchall(all_tags_query)]


def get_all_sections():
    """get all section_ids from past queried and clicked stuff table as well as t_p_s table"""
    all_sections_query = """
        SELECT DISTINCT material_id, material_type
        FROM ui_uihk_recsys_u_q
        UNION
        SELECT DISTINCT material_id, material_type
        FROM ui_uihk_recsys_u_c
        UNION
        SELECT DISTINCT section_id, material_type
        FROM ui_uihk_recsys_t_p_s
    """
    return DB.fetchall(all_sections_query)

def get_all_sections_for_crs(crs_id):
    """Make an SQL Join of ui_uihk_recsys_t_p_s table with ui_uihk_recsys_tags table (to get crs_id field) and return all section_ids of the crs_id with their material_type and tag_id"""
    all_sections_for_crs_query = """
        SELECT section_id, material_type
        FROM ui_uihk_recsys_t_p_s
        JOIN ui_uihk_recsys_tags
        ON ui_uihk_recsys_t_p_s.tag_id = ui_uihk_recsys_tags.tag_id
        WHERE crs_id = {}
    """.format(crs_id)
    return DB.fetchall(all_sections_for_crs_query)

def get_all_sections_and_tags_for_crs(crs_id):
    """Make an SQL Join of ui_uihk_recsys_t_p_s table with ui_uihk_recsys_tags table and return all section_ids with their material types plus the tag_id"""
    all_sections_and_tags_for_crs_query = """
        SELECT ui_uihk_recsys_t_p_s.section_id, ui_uihk_recsys_t_p_s.material_type, ui_uihk_recsys_tags.tag_id
        FROM ui_uihk_recsys_t_p_s
        JOIN ui_uihk_recsys_tags
        ON ui_uihk_recsys_t_p_s.tag_id = ui_uihk_recsys_tags.tag_id
        WHERE crs_id = {}
    """.format(crs_id)
    return DB.fetchall(all_sections_and_tags_for_crs_query)

def get_sections_for_tag(tag_id):
    """Returns all section_ids that have the tag_id"""
    sections_for_tag_query = """
        SELECT section_id, material_type
        FROM ui_uihk_recsys_t_p_s
        WHERE tag_id = {}
    """.format(tag_id)
    return DB.fetchall(sections_for_tag_query)
    
def section_to_identifier(section_id, material_type):
    """Returns the identifier of the section_id, material_type tuple"""
    return str(section_id) + "_" + str(material_type)

def identifier_to_section(identifier):
    """
    Returns the section_id, material_type tuple of the identifier. 
    Not quite the reverse function, as it does not convert back to 2 integers, but changing it now would break the code.
    """
    return identifier.split("_")
    
def get_all_item_identifiers():
    sections = get_all_sections()
    return [section_to_identifier(*section) for section in sections]

def get_first_timestamp_for_crs(crs_id):
    """Returns the timestamp of the first entry in ui_uihk_recsys_u_c for the given crs_id"""
    first_timestamp_query = """
        SELECT timestamp
        FROM ui_uihk_recsys_u_q
        WHERE crs_id = {}
        ORDER BY timestamp ASC
        LIMIT 1
    """.format(crs_id)
    return DB.fetchall(first_timestamp_query)[0][0]

def get_first_timestamp():
    """Returns the timestamp of the first entry in ui_uihk_recsys_u_c"""
    first_timestamp_query = """
        SELECT timestamp
        FROM ui_uihk_recsys_u_q
        ORDER BY timestamp ASC
        LIMIT 1
    """
    return DB.fetchall(first_timestamp_query)[0][0]


def create_tag_pretraining_data(n=100):
    #Create dictionary with all tags as keys and a list of all section_ids that have the tag as values
    all_tags = get_all_tags()
    tag_dict = {tag: get_sections_for_tag(tag) for tag in all_tags}
    tag_pretraining_inputs = [(tag, section_to_identifier(*section)) for tag in tag_dict for section in tag_dict[tag]]
    final_data = []
    for i in range(n):
        input_item = random.choice(tag_pretraining_inputs)
        tag = input_item[0]
        targets = [section_to_identifier(*section) for section in tag_dict[tag]]
        if len(targets) > 0:
            final_data.append({"TAG_INPUT": [tag], "PAST_QUERIES": [], "PAST_RECOMMENDATIONS": [], "TARGET_RECOMMENDATIONS": [(target, 1) for target in targets], "QUERIED_SECTIONS": [(input_item[1], 0)]})
    return final_data

def collect_data_for_query(usr_id, crs_id, section_ids, material_types, timestamp, retrieve_targets=False):
    """
    Timestamp is the time of the query. Set retrieve_targets to True for training data retrieval. Otherwise there are no targets.
    
    Retrieve following:
        TAG_INPUT: Get all tag_ids that belong to the section_ids (of the material_types) from the database.
        PAST QUERIES: Get all entries from ui_uihk_recsys_u_q (query table) that has a timestamp PRIOR to the query table set timestamp
        PAST RECOMMENDATIONS: Get all entries from ui_uihk_recsys_u_c (clicked stuff table) that has a timestamp PRIOR to the query table set timestamp
        TARGET RECOMMENDATIONS:  Get all entries from ui_uihk_recsys_u_c (clicked stuff table) that has a timestamp within a couple hours after the timestamp
    Combine into dictionary:
        {
            QUERIED_SECTIONS: [(material_type, section_id, timestamp), (...), ...],
            TAG_INPUT: [tag_ids],
            PAST_QUERIES: [(material_type, section_id, timestamp), (...), ...],
            PAST_RECOMMENDATIONS: [(material_type, section_id, timestamp), (...), ...],
            TARGET_RECOMMENDATIONS: [(material_type, section_id, timestamp), (...), ...]
        } 
    """
    tag_input = get_tags_for_materials(section_ids, material_types)
    past_queries = get_past_queries(usr_id, crs_id, timestamp)
    past_recommendations = get_past_recommendations(usr_id, crs_id, timestamp)
    queried_sections = list(zip(section_ids, material_types))


    # Combine the results into a dictionary
    result_dict = {
        'QUERY': (usr_id, crs_id, section_ids, material_types, timestamp),
        'TAG_INPUT': [tag[0] for tag in tag_input],
        'PAST_QUERIES': past_queries,
        'PAST_RECOMMENDATIONS': past_recommendations,
        'QUERIED_SECTIONS': queried_sections,
    }
    if retrieve_targets:
        target_recommendations = get_target_recommendation(usr_id, crs_id, timestamp, TARGET_RECOMMENDATION_LIMIT) # within 24 hours 
        result_dict['TARGET_RECOMMENDATIONS'] = target_recommendations

    return result_dict

def parse_datadict(data_dict):
    
    #Parse material ids and types into item identifiers
    for key in ["PAST_QUERIES", "PAST_RECOMMENDATIONS", "TARGET_RECOMMENDATIONS", 'QUERIED_SECTIONS']:
        if key not in data_dict:
            continue
        datalist = data_dict[key]
        datalist = [(section_to_identifier(data[0], data[1]), *data[2:]) for data in datalist]
        data_dict[key] = datalist
    
    #PAST_QUERIES: [(item_identifier, timestamp), (...), ...],
    
    
    #Convert timestamps to relative timestamps
    global FIRST_TIMESTAMP
    if FIRST_TIMESTAMP == None:
        #FIRST_TIMESTAMP = get_first_timestamp_for_crs(data_dict['QUERY'][1])-1
        FIRST_TIMESTAMP = get_first_timestamp()-1
    
    current_timestamp = data_dict['QUERY'][-1]
    data_dict["PAST_QUERIES"] = [(item_identifier , max((timestamp-FIRST_TIMESTAMP)/(current_timestamp-FIRST_TIMESTAMP), MIN_IMPORTANCE)) for item_identifier, timestamp in data_dict["PAST_QUERIES"]]
    data_dict["PAST_RECOMMENDATIONS"] = [(item_identifier , max((timestamp-FIRST_TIMESTAMP)/(current_timestamp-FIRST_TIMESTAMP), MIN_IMPORTANCE)) for item_identifier, timestamp in data_dict["PAST_RECOMMENDATIONS"]]
    if "TARGET_RECOMMENDATIONS" in data_dict:
        max_timestamp = current_timestamp + TARGET_RECOMMENDATION_LIMIT 
        data_dict["TARGET_RECOMMENDATIONS"] = [(item_identifier , (max_timestamp-timestamp)/(max_timestamp-current_timestamp)) for item_identifier, timestamp in data_dict["TARGET_RECOMMENDATIONS"]]
    return data_dict

#print(parse_datadict(collect_data_for_query(6, 100, [7,3,4],[0,6,6], 1700640797,  retrieve_targets=True)))
