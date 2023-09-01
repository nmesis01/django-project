from django.shortcuts import render,redirect
from .models import Word,Definitions,Examples
from django.contrib.auth.decorators import login_required
from django.contrib import messages
import requests
# Create your views here.
def home(request):
    return render(request,"home/index.html")
@login_required
def oxford(request):
    def show_page(content={}):
        word_db = Word.objects.filter(author=request.user).order_by("id").reverse()
        words_list = {}
        definitions_id = []
        examples_id = []
        for i in word_db:
            parsed = i.word.split("_")
            def_db = Definitions.objects.filter(word = i)
            def_dict = {}
            for m in def_db:
                example_db = Examples.objects.filter(definition = m)
                example_dict = {}
                definitions_id.append(m.id)
                if example_db:
                    for n in example_db:
                        examples_id.append(n.id)
                        example_dict.update({n.example:{"id":n.id}})
                        def_dict.update({m.definition:{"id":m.id,"examples":example_dict}})
                else:
                    def_dict.update({m.definition:{"id":0,"examples":{}}})
            words_list.update({parsed[0]:{"tag":parsed[1],"definitions":def_dict}})
        content.update({"words":words_list,"definitions_id":definitions_id,"examples_id":examples_id})
        return render(request,"home/oxford.html",context=content)
    #search method
    if request.method == "POST":
        words = request.POST["word_input"]
        result = requests.post("https://oxfapi.azurewebsites.net/search",data={"word":words})
        if(result.status_code==200):
            result = result.json()
            if len(Word.objects.filter(author=request.user,word=result["word"])) == 0:
                word = Word(word=result["word"],author=request.user)
                word.save()
                for i in result["definitions"]:
                    definition = Definitions(definition=i,word_id = word.id)
                    definition.save()
                    for m in result["examples"][result["definitions"].index(i)]:
                        example = Examples(example=m,definition_id=definition.id)
                        example.save()
                url = f"/oxford/?edit={result['word']}"
                return redirect(url)
            else:
                messages.add_message(request,messages.WARNING,message=f"There is {words} in your list!")
        else:
            result = requests.get("https://oxfapi.azurewebsites.net/suggest",params={"word" :words})
            context = {"suggest_word":result.json()["word"],}
            return show_page(content=context)
    #edit method
    if request.method == "GET" and request.GET.get("edit") and request.GET["edit"]!="":
        parse = request.GET["edit"].split("_")
        if(len(parse) == 1):
            parse.append("")
        context = {"edit_word":parse[0],"edit_tag":parse[1],}
        return show_page(context)
    #update method
    if request.method == "GET" and request.GET.get("update") and request.GET.getlist("definitions[]"):
        definitions = request.GET.getlist("definitions[]")
        examples = request.GET.getlist("examples[]")
        word = request.GET.get("update")
        word_obj = Word.objects.filter(author=request.user,word=word).first()
        def_obj = Definitions.objects.filter(word = word_obj.id)
        for i in def_obj:
            if(str(i.id) not in definitions):
                i.delete()
            else:
                example_obj = Examples.objects.filter(definition=i.id)
                for m in example_obj:
                    if(str(m.id) not in examples):
                        m.delete()
    #delete method
    if request.method == "GET" and request.GET.get("delete") and request.GET["delete"] != "":
        word = request.GET["delete"]
        Word.objects.filter(author = request.user,word=word).first().delete()
        return redirect("oxford")
    #search method
    if request.method == "GET" and request.GET.get("search") and request.GET["search"] != "":
        word = request.GET["search"]
        context = {"search_word":word}
        return show_page(context)
    #similar
    if request.method == "GET" and request.GET.get("similar") and request.GET["similar"] != "":
        words = request.GET["similar"].split("_")[0]
        result = requests.get("https://oxfapi.azurewebsites.net/getsimilar",params={"word":words})
        similar_words = {}
        split_word = ["x","y"]
        for i in result.json()["words"]:
            if "_" in i and i.split("_")[1] != "":
                split_word = i.split("_")
                split_word[1] = f"({split_word[1]})"
            elif "_" in i and i.split("_")[1] == "":
                split_word[0] = i.split("_")[0]
                split_word[1] = ""
            else:
                split_word[0] = i
                split_word[1] = ""
            similar_words.update({i:{"word":split_word[0],"tag":split_word[1],"label":result.json()["labels"][result.json()["words"].index(i)]}})
        context = {
            "similar_word":similar_words,
        }
        return show_page(context)
    #find
    if request.method == "GET" and request.GET.get("find") and request.GET["find"]!="":
        words = request.GET["find"]
        result = requests.get("https://oxfapi.azurewebsites.net/find",params={"word":words})
        if(result.status_code==200):
            result = result.json()
            if len(Word.objects.filter(author=request.user,word=result["word"])) == 0:
                word = Word(word=result["word"],author=request.user)
                word.save()
                for i in result["definitions"]:
                    definition = Definitions(definition=i,word_id = word.id)
                    definition.save()
                    for m in result["examples"][result["definitions"].index(i)]:
                        example = Examples(example=m,definition_id=definition.id)
                        example.save()
                url = f"/oxford/?edit={result['word']}"
                return redirect(url)
            else:
                messages.add_message(request,messages.WARNING,message=f"There is {words} in your list!")
        else:
            result = requests.get("https://oxfapi.azurewebsites.net/suggest",params={"word":words})
            context = {
                "suggest_word":result.json()["word"],
            }
            return show_page(context)
    return show_page()