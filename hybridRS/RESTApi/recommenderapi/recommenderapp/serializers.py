from rest_framework import serializers

class MLModelInputSerializer(serializers.ModelSerializer):
    userId = serializers.IntegerField()
    crsId = serializers.IntegerField()
    timestamp = serializers.IntegerField()
    sectionIds = serializers.ListField(child=serializers.IntegerField(), required=False)
    materialTypes = serializers.ListField(child=serializers.IntegerField(), required=False)

class PredictionSerializer(serializers.Serializer):
    sectionId = serializers.IntegerField()
    materialId = serializers.IntegerField()
    score = serializers.FloatField()

class MLModelOutputSerializer(serializers.ModelSerializer):
    usrId = serializers.IntegerField()
    crsId = serializers.IntegerField() 
    predictions = serializers.ListField(child=PredictionSerializer())
#   "crs_id": crs_id,
#   "predictions": [{"section_id": section_id, "material_type": material_type, "score": score},
#                   {"section_id": section_id, "material_type": material_type, "score": score},
#                   ...
#                  ]
 