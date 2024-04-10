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
    serializer = MLModelInputSerializer(data=request.data)
    if serializer.is_valid():
        model_handler = ModelHandlerAdmin.get_model_handler(
            crs_id=serializer.validated_data['crsId'],
            encoder_types=serializer.validated_data['encoderTypes']
        )
        model_output = model_handler(
            serializer.validated_data['usrId'],
            serializer.validated_data['crsId'],
            serializer.validated_data['sectionIds'],
            serializer.validated_data['materialTypes'],
            serializer.validated_data['timestamp']
        )
        
        output_serializer = MLModelOutputSerializer(data=model_output)
        if output_serializer.is_valid():
            return JsonResponse(output_serializer.data, status= status.HTTP_200_OK)
        return JsonResponse(output_serializer.errors, status= status.HTTP_500_INTERNAL_SERVER_ERROR)
    return JsonResponse(serializer.errors, status=status.HTTP_400_BAD_REQUEST)


@api_view(['PUT'])
def ml_model_train(request):
    #update the model to train
    serializer = MLModelTrainSerializer(data = request.data)
    if serializer.is_valid():
        model_handler = ModelHandlerAdmin.get_model_handler(
            crs_id=serializer.validated_data['crsId'],
            encoder_types=serializer.validated_data['encoderTypes'],
            refresh=True
        )
        model_handler.train();
        return JsonResponse('Model has successfully been trained!' ,status=status.HTTP_204_NO_CONTENT, safe=False)
    return JsonResponse(serializer.erors, status=status.HTTP_400_BAD_REQUEST)


class ExceptionLoggingMiddleware():
    def __init__(self, get_response):
        self.get_response = get_response

    def __call__(self, request):

        # Code to be executed for each request before
        # the view (and later middleware) are called.
        print(request.body)
        print(request.scheme)
        print(request.method)
        print(request.META)

        response = self.get_response(request)

        return response