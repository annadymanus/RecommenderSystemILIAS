import os
from dataRetriever import Database

#   baseline model based on the interests of students
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


