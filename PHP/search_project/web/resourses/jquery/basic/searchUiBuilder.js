$.fn.searchUiBuilder = function (settings) {
    var defaults = {

    };
    $.extend(true, defaults, settings);
    var container = $(this);
    var currentSearchData = {};
    function initPagination(results,itemsPerPage,currentPage){
        var pages = Math.ceil(results/itemsPerPage);
        $(".pages-top").pagination({
            items: pages,
            cssStyle: 'light-theme',
            prevText: "Предишна",
            nextText: "Следваща",
            currentPage: currentPage,
            onPageClick:function(pageNumber, event){
                currentSearchData.page = pageNumber;
                performQuery(currentSearchData);
            }
        });
        $(".pages-bottom").pagination({
            items: pages,
            cssStyle: 'light-theme',
            prevText: "Предишна",
            nextText: "Следваща",
            currentPage: currentPage,
            onPageClick:function(pageNumber, event){
                currentSearchData.page = pageNumber;
                performQuery(currentSearchData);
            }
        });
    }
    function generateRangeField(from,to,field){
        if(from == "")
            from="*";
        if(to == "")
            to="*";
        if(field == "year"){
            if(from !="*")
                from = from + "-01-01T00:00:00Z";
            if(to !="*")
                to = to+"-12-31T23:59:59Z";
        }
        return (field+":["+from+" TO "+to+"]");
    }

    function generateQueryParams(text){
        var keywords =  container.find("#keywords-field-option").prop("checked");
        var description =  container.find("#description-field-option").prop("checked");
        var priceFrom = container.find("#price-field-from").val();
        var priceTo = container.find("#price-field-to").val();
        var distanceFrom = container.find("#distance-field-from").val();
        var distanceTo = container.find("#distance-field-to").val();
        var yearFrom =  container.find("#year-field-from").val();
        var yearTo =  container.find("#year-field-to").val();
        var highlight = container.find("#highlight-field-option").prop("checked");
        var title = container.find("#title-field-option").prop("checked");
        var all = container.find("#all-field-option").prop("checked");
        var price = generateRangeField(priceFrom,priceTo,"price");
        var year = generateRangeField(yearFrom,yearTo,"year");
        var distance = generateRangeField(distanceFrom,distanceTo,"km");
        var results =  container.find("#results-field-from").val();
        if(results<=0||results=="")
            results = 15;
        if(text == "")
            text = "*:*";
        var jsonData = {
            "text":text,
            "price": price,
            "keywords": keywords,
            "all":all,
            "results":results,
            "title": title,
            "distance":distance,
            'year':year,
            'highlight':highlight,
            "description": description,
            "page":1,
            "pageItems":results
        };
        return jsonData;
    }

    function createResultField(title,description,keywords,url,price,km,year,highlights){
        $.each(highlights,function(key,val){
            if(key=="title")
                title = val[0];
            if(key=="keywords")
                keywords = val[0];
            if(key=="description")
                description = val[0];
        });
        var result = $("<div class='search-result'>" +
            "<div class='result-title'><span>"+title+"</span></div>" +
            "<div class='result-description'><b>Описание</b>: "+description+"</div>" +
            "<div class='result-keywords'><span><b>Ключови думи:</b> "+keywords+"</span></div>"+
            "<div class='result-price'><span><b>Цена:</b> "+price+"</span></div>"+
            "<div class='result-year'><span><b>Година:</b> "+year+"</span></div>"+
            "<div class='result-distance'><span><b>Километри:</b> "+km+"</span></div>"+
            "<div class='result-visit'><a href='"+url+"'>Научи повече</a></div>"+
            "</div>" +
            "<div class='result-separator'></div>");
        $(".search-results-holder .results-container").append(result);
    }
    function performQuery(parameters){
        $.ajax({
            url: "services/getResults",
            type:"GET",
            data:parameters,
            dataType: "json"
        }).done(function(result) {
            var docsFound = result.response.numFound;
            var docs = $(result.response.docs);
            var highlights = $(result.highlighting);
            $(".search-results-holder").show();
            $(".search-results-holder .results-container *").remove();
            var results =  container.find("#results-field-from").val();
            if(results<=0||results=="")
                results = 15;
            initPagination(docsFound,results,parameters.page);
            if(docs.length == 0){
                $(".search-results-holder .results-container").append("<div class='search-result-empty'>Няма открити резултати.</div>");
            }
            docs.each(function(key,val){
                var id = val.id.toString();
                var lights = {};
                if(typeof (highlights[0]) != "undefined")
                    lights = highlights[0][id];
                var title = val.title[0];
                var description = val.description;
                var keywords = val.keywords;
                var url = val.url;
                var price = val.price+" "+val.currency;
                var km = val.km;
                var year = val.year;
                createResultField(title,description,keywords,url,price,km,year,lights);
            });
        });
    }

    var initEvents = function(){
        $(".submit-button").click(function() {
            var text = container.find(".search-text").val();
            var data = generateQueryParams(text);
            currentSearchData = data;
            performQuery(currentSearchData);
        });

        $("#keywords-field-option,#price-field-option,#title-field-option,#description-field-option").change(function(){
            if($(this).prop("checked")){
                container.find("#all-field-option").prop("checked",false);
            }
        });

        $("#all-field-option").change(function(){
            if($(this).prop("checked")){
                container.find("#keywords-field-option").prop("checked",false);
                container.find("#description-field-option").prop("checked",false);
                container.find("#price-field-option").prop("checked",false);
                container.find("#title-field-option").prop("checked",false);
            }
        });
        $(".advanced-search-button").click(function(){
            container.find(".advanced-search").slideToggle();
        });
    };

    var buildHtml = function(){
        container.append('<div class="input-holder">'+
            '<div class="input">'+
            '<input type="text" class="search-text"/>'+
            '</div><div class="submit-button">'+
            'Търсене'+
            '</div>'+
            '<div class="advanced-search">'+
            '<div class="advanced-search-section">'+
            '<span class="advanced-search-section-title">'+
            'Tърси в:'+
            '</span>'+
        '<div class="search-option">'+
            '<input type="checkbox" id="all-field-option">'+
            '<label class="noselect" for="all-field-option">'+
            'Всички'+
            '</label>'+
        '</div>'+
            '<div class="search-option" >'+
            '<input type="checkbox" id="title-field-option" >'+
            '<label class="noselect" for="title-field-option">'+
            'Заглавие'+
            '</label>'+
            '</div>'+
            '<div class="search-option">'+
            '<input type="checkbox"  id="keywords-field-option" >'+
            '<label class="noselect" for="keywords-field-option">'+
            'Ключови Думи'+
        '</label>'+
        '</div>'+
        '<div class="search-option">'+
            '<input type="checkbox" id="description-field-option">'+
            '<label class="noselect" for="description-field-option">'+
            'Описание'+
            '</label>'+
            '</div>'+
            '</div>'+
            '<div class="advanced-search-section">'+
            '<span class="advanced-search-section-title">'+
            'Цена:'+
        '</span>'+
        '<div class="search-option">'+
            '<input type="text" placeholder="От" id="price-field-from">'+
            '<input type="text" placeholder="До" id="price-field-to">'+
            '</div>'+
            '</div>'+
            '<div class="advanced-search-section">'+
            '<span class="advanced-search-section-title">'+
            'Пробег:'+
        '   </span>'+
                '<div class="search-option">'+
                    '<input type="text" placeholder="От" id="distance-field-from">'+
                    '<input type="text" placeholder="До" id="distance-field-to">'+
                '</div>'+
            '</div>'+
            '<div class="advanced-search-section">'+
                '<span class="advanced-search-section-title">'+
                'Година на производство:'+
                '</span>'+
                '<div class="search-option">'+
                    '<input type="text" placeholder="От" readonly id="year-field-from">'+
                    '<input type="text" placeholder="До" readonly id="year-field-to">'+
                '</div>'+
            '</div>'+
            '<div class="advanced-search-section">'+
            '<span class="advanced-search-section-title">'+
            'Други настройки:'+
            '</span>'+
            '<div class="search-option">'+
            '<input type="checkbox" id="highlight-field-option">'+
            '<label class="noselect" for="highlight-field-option">'+
            'Оцветяване'+
            '</label>'+
            '</div>'+
            '</div>'+
            '<div class="advanced-search-section">'+
            '<span class="advanced-search-section-title">'+
            'Брой резултати на страница:'+
            '</span>'+
            '<div class="search-option">'+
            '<input type="text" placeholder="15" id="results-field-from">'+
            '</div>'+
            '</div>'+
            '</div>'+
            '<div class="advanced-search-button">'+
        'Настройки за търсене'+
        '</div>'+
        '</div>'+
            '<div class="search-results-holder">'+
            '<div class="pagination">'+
            '<div class="paging-container-top">'+
            '<div class="pages-top">'+
            '</div>'+
            '</div>'+
            '</div>'+
            '<div class="results-container">'+
            '</div>'+
            '<div class="pagination">'+
            '<div class="paging-container-bottom">'+
            '<div class="pages-bottom">'+
            '</div>'+
            '</div>'+
            '</div>'+
            '</div>');
        $("#all-field-option").prop("checked",true);
        container.find("#year-field-from,#year-field-to").datepicker({
            format:"YYYY"
        });
    };

    buildHtml();
    initEvents();
};