from rest_framework import serializers

class MLModelInputSerializer(serializers.Serializer):
    usrId = serializers.IntegerField()
    crsId = serializers.IntegerField()
    timestamp = serializers.IntegerField()
    sectionIds = serializers.ListField(child=serializers.IntegerField(), required=False)
    materialTypes = serializers.ListField(child=serializers.IntegerField(), required=False)
    encoderTypes = serializers.ListField(
        child=serializers.ChoiceField(choices=["tag", "recquery", "pastquery", "pastclicked"]),
        required=True,
    )

class MLModelTrainSerializer(serializers.Serializer):
    crsId = serializers.IntegerField()
    encoderTypes = serializers.ListField(
        child=serializers.ChoiceField(choices=["tag", "recquery", "pastquery", "pastclicked"]),
        required=True,
    )

class PredictionSerializer(serializers.Serializer):
    section_id = serializers.IntegerField()
    material_type = serializers.IntegerField()
    score = serializers.SerializerMethodField()
    
    def get_score(self, instance):
        #Convert the tensor value to a float (no longer necessary, but kept for compatibility with the old model handler)
        return float(instance['score'])
    
    def to_internal_value(self, data):
        data['section_id'] = int(data['section_id'])
        data['material_type'] = int(data['material_type'])
        return data

class MLModelOutputSerializer(serializers.Serializer):
    usr_id = serializers.IntegerField()
    crs_id = serializers.IntegerField() 
    predictions = PredictionSerializer(many=True)

#   "crs_id": crs_id,
#   "predictions": [{"section_id": section_id, "material_type": material_type, "score": score},
#                   {"section_id": section_id, "material_type": material_type, "score": score},
#                   ...
#                  ]
 