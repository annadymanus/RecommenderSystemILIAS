try:
    from mlmodel.model import ModelHandler
    from mlmodel.dataRetriever import get_all_tags, get_all_item_identifiers
except ImportError:
    from model import ModelHandler
    from dataRetriever import get_all_tags, get_all_item_identifiers
from typing import Optional
import threading


class ModelHandlerAdmin:
    _model_handler : Optional[ModelHandler] = None
    _lock = threading.Lock()
    
    @classmethod
    def get_model_handler(cls, crs_id, encoder_types=["tag", "recquery", "pastquery", "pastclicked"], refresh=False):
        with cls._lock:
            # If the model_handler is not initialized, create a new instance
            if not cls._model_handler or refresh or set(encoder_types) != set(cls._model_handler.encoder_types_to_index.keys()):
                #This instantiates the ModelHandler with 4 different encoders
                cls._model_handler = ModelHandler(encoder_types=encoder_types,
                             tags=get_all_tags(),
                             items=get_all_item_identifiers(),
                             crs_id=crs_id,
                             load_checkpoint=refresh)
            return cls._model_handler

    



#MODEL_HANDLER= ModelHandler(encoder_types=["tag","recquery", "pastquery", "pastclicked"],
#                             tags=get_all_tags(),
#                             items=get_all_item_identifiers(),
#                             crs_id=100)
# This is how to call the handler to get the recommendations
# usr_id, crs_id, queried section_ids, corresponding material_types, timestamp of the query
# The relevant information is then queried from the database and the model predicts the recommendations
#result_dict = MODEL_HANDLER(6, 100, [7,3,4],[0,6,6], 1700640797)
#print(result_dict)
#Spits out dict with:
# {
#   "usr_id": usr_id, 
#   "crs_id": crs_id,
#   "predictions": [{"section_id": section_id, "material_type": material_type, "score": score},
#                   {"section_id": section_id, "material_type": material_type, "score": score},
#                   ...
#                  ]
# }

# IF NEW TAGS OR ITEMS ARE ADDED, the modelhandler does not need to be reinstantiated. 
# The first time the handler is called and "observes" a new itemID or tag, it will add it to the model.
# Removed itemIDs or Tags are not removed from the model until retraining, but will simply be ignored in the predictions.

#MODEL_HANDLER.train() #Retrains the model with the new data available in the DB


#When someone clicks on a recommendation, the model should be updated with the new data
#Work in progress
#MODEL_HANDLER.update(...)