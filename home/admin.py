from django.contrib import admin
from .models import Word,Definitions
# Register your models here.
@admin.register(Word)
class Word_admin(admin.ModelAdmin):
    list_display = ("Kelime_İsmi","author")
    def Kelime_İsmi(self,obj):
        word = obj.word
        splitted = word.split("_")[0]
        return splitted
    