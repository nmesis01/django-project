from django.db import models
from django.contrib.auth.models import User

# Create your models here.
class Word(models.Model):
    word = models.CharField(max_length=120,verbose_name="Kelime İsmi")
    author = models.ForeignKey(User,on_delete=models.CASCADE,verbose_name="User")
    def __str__(self):
        return self.word.split("_")[0]
class Definitions(models.Model):
    definition = models.TextField()
    word = models.ForeignKey(Word,on_delete=models.CASCADE)
class Examples(models.Model):
    example = models.TextField()
    definition = models.ForeignKey(Definitions,on_delete=models.CASCADE)