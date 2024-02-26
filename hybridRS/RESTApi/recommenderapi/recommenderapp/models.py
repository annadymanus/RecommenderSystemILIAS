from django.db import models

class ModelOutput(models.Model):
    #TODO add meaningfull paramters here
    name = models.CharField(max_length=200)
    description = models.CharField(max_length=300)

    def __str__(self):
        return self.name + " " + self.description

