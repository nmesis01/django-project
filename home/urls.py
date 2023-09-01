from django.urls import path
from .views import home,oxford
urlpatterns = [
    path('', home,name="home"),
    path("oxford/",oxford,name="oxford")
]
