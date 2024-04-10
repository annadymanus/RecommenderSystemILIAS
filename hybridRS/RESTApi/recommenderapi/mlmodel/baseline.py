import dataRetriever as dR
import pandas as pd
import numpy as np
from collections import defaultdict
from sklearn.cluster import DBSCAN
from sklearn.neighbors import NearestNeighbors
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.metrics.pairwise import euclidean_distances
import json


crs_id = 132


def create_colaborative_dataframe(crs_id):
    #get all columns for dataframe the consist of the counts for the single sections as well as the tags they belong to
    sections_raw_array = dR.get_all_sections_and_tags_for_crs(crs_id)
    section_tag_array = []

    # Initialize an empty dictionary to store mapping of section to tags
    section_to_tags_map = {}

    for item in sections_raw_array:
        section = dR.section_to_identifier(item[0], item[1])
        if section not in section_to_tags_map:
            section_to_tags_map[section] = [item[2]]
        else:
            section_to_tags_map[section].append(item[2])


    for material_section in sections_raw_array:
        section_tag_array.append((dR.section_to_identifier(material_section[0], material_section[1]),material_section[2]))

    sections_set = sorted(set(x[0] for x in section_tag_array))
    tags_set = sorted(set(x[2] for x in sections_raw_array))

    # Create empty DataFrame with columns
    columns = ['usr_id'] + [f'section_{section}' for section in sections_set] + [f'tag_{tag_id}' for tag_id in tags_set]
    colab_df = pd.DataFrame(columns=columns)

    # Create a defaultdict to store counts based on the first three integers
    count_dict = defaultdict(int)

    recommendations_raw_array = dR.get_all_recommendations_for_crs(crs_id)
    # Iterate through the original list and count occurrences
    for item in recommendations_raw_array:
        key = item[:3]  # Use the first three integers as the key
        count_dict[key] += 1

    # Create the new array with counts replacing the fourth integer
    recommendation_array = [(key[0], key[1], key[2], count) for key, count in count_dict.items()]

    # Fill DataFrame with values based on recommendations data
    for recommendation in recommendation_array:
        recommendation_usrId = recommendation[0]
        recommendation_section = dR.section_to_identifier(recommendation[1], recommendation[2])
        recommendation_section_encoded = f'section_{recommendation_section}'
    
        #check whether there already is an entry for the user
        if recommendation_usrId in colab_df['usr_id'].values:
            # add the clicks of the usr for the section
            colab_df.loc[colab_df['usr_id'] == recommendation_usrId, recommendation_section_encoded] += recommendation[3]
            # add the clicks of the usr to all belonging tags
            for tag_id in section_to_tags_map[recommendation_section]:
                colab_df.loc[colab_df['usr_id'] == recommendation_usrId, f'tag_{tag_id}'] += recommendation[3]
        else:
            # Initialize a new row with 0s for NaN values
            new_row = {'usr_id': recommendation_usrId, recommendation_section_encoded: recommendation[3]}
            for tag_id in section_to_tags_map[recommendation_section]:
                new_row[f'tag_{tag_id}'] = recommendation[3]
            # Convert the new row to DataFrame and concatenate with colab_df
            new_row_df = pd.DataFrame([new_row], columns=colab_df.columns)
            # Fill only the NaN values in the new row with 0s
            new_row_df.fillna(0, inplace=True)
            colab_df = pd.concat([colab_df, new_row_df], ignore_index=True)

    # normalize the columns
    # Extract usr_id column
    usr_id_column = colab_df['usr_id']

    # Drop usr_id column for normalization
    columns_to_normalize = colab_df.columns.difference(['usr_id'])

    # Normalize columns except usr_id using Min-Max scaling
    colab_df[columns_to_normalize] = colab_df[columns_to_normalize].apply(lambda x: (x - x.min()) / (x.max() - x.min()))
    colab_df.fillna(0, inplace=True)

    # Re-insert usr_id column
    colab_df['usr_id'] = usr_id_column

    return colab_df

# user-based collaborative filterint
# options-for similarityMetric: 'euclidean', 'cosine', 'centered_cosine'
def kNN_based_colabfiltering(usr_id, colab_df, similarityMetric):
    # 1. find out similarity between users: (scipy) spatial.distance.euclidean, (scipy: cosine similarity) spatial.distance.cosine
    k = 3  # Number of neighbors
    usr_id_column = colab_df['usr_id']
    recommendations = colab_df.drop(columns=['usr_id'])
    
    if(similarityMetric == 'euclidean'):
        nbrs = NearestNeighbors(n_neighbors=k+1, metric='euclidean').fit(recommendations)
        distances, indices = nbrs.kneighbors(recommendations)
        distances = distances[:, 1:]  # Exclude self from distances
        indices = indices[:, 1:]      # Exclude self from neighbors

    elif(similarityMetric == 'centered_cosine'):
        # center data
        centered_recommend = recommendations - recommendations.mean()
        recommendations = centered_recommend
        # compute cosine distance by going to the next case
        pass

    elif(similarityMetric == 'cosine'):
        nbrs = NearestNeighbors(n_neighbors=k+1, metric='cosine').fit(recommendations)
        distances, indices = nbrs.kneighbors(recommendations)
        distances = distances[:, 1:]  # Exclude self from distances
        indices = indices[:, 1:]      # Exclude self from neighbors
    
    else:
        raise ValueError("unkown metric was provided")

    # 3. Calculate Rating (weighted average: make more simular users have more impact on rating)
    similarity_scores = 1 / (1 + distances)

    #Find nearest neighbors of the given user
    user_index = recommendations.index[colab_df['usr_id'] == usr_id].tolist()[0]
    user_neighbors_indices = indices[user_index]
    user_neighbors_scores = similarity_scores[user_index]

    # Weighted average of item ratings of neighbors
    weighted_ratings = np.zeros(recommendations.shape[1])  # Initialize array for weighted ratings
    total_similarity = np.sum(user_neighbors_scores)

    
    for neighbor_idx, neighbor_score in zip(user_neighbors_indices, user_neighbors_scores):
        weighted_ratings += recommendations.iloc[neighbor_idx] * neighbor_score

    # Normalize by total similarity
    predicted_ratings = weighted_ratings / total_similarity

    # Sort items based on predicted ratings 
    # Boolean mask for elements starting with 'tag_'
    sorted_items = predicted_ratings.sort_values(ascending=False)

    # Get top 5 recommended items ignore all that are of type tag_<tag_id>
    top_items = sorted_items.index.tolist()
    mask = np.array([not s.startswith('tag_') for s in top_items])
    top_items_filtered = np.array(top_items)[mask]
    
    return top_items_filtered


def create_collab_baseline(crs_id):
    colab_df = create_colaborative_dataframe(crs_id)
    
    user_pred = {}
    for usr_id in colab_df['usr_id']:
        user_pred[usr_id] = [] 
        usr_recs = kNN_based_colabfiltering(usr_id, colab_df, 'cosine')
        for rec in usr_recs:
            section_id, material_type = _decode_section_identifiers(rec[len('section_'):])
            user_pred[usr_id].append((section_id, material_type))
    return user_pred


def _decode_section_identifiers(identifier):
    """Decodes the identifier string into section_id and material_type"""
    section_id, material_type = identifier.split("_")
    return int(section_id), int(material_type)


colab_df = create_colaborative_dataframe(crs_id)
print(colab_df)

collab_dict = create_collab_baseline(crs_id)
print(collab_dict)

#dump the prediction into a json file
json.dump(collab_dict, open(f"baseline_collab.json","w"))

