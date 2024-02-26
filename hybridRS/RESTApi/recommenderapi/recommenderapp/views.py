from django.shortcuts import render

# Create your views here.
from django.http import JsonResponse
from .serializers import *
from rest_framework.decorators import api_view
from rest_framework import status
from django.conf import settings
from mlmodel.handler import ModelHandlerAdmin

@api_view(['POST'])
def ml_model_rec(request):
    
    #get the Model input from the request parameters
    serializer = MLModelInputSerializer(data = request.data)
    if serializer.is_valid():
        model_handler = ModelHandlerAdmin.get_model_handler()
        model_output = model_handler(serializer.data.usrId, serializer.data.crsId, serializer.data.sectionIds, serializer.data.materialTypes)
        output_serializer = MLModelOutputSerializer(model_output)
        print(model_output)
        if output_serializer.is_valid():
            return JsonResponse(output_serializer.data, status= status.HTTP_200_OK)
        return JsonResponse(serializer.data, status= status.HTTP_500_INTERNAL_SERVER_ERROR)
    return JsonResponse(serializer.errors, status=status.HTTP_400_BAD_REQUEST)
    
    #serialize the output
    #serializer2 = ModelOutputSerializer(outputs, many=True)
    #return json
    #return JsonResponse({'output': serializer2.data})
