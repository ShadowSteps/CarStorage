$.fn.searchUiBuilder = function (settings) {
    var defaults = {};
    $.extend(true, defaults, settings);
    var container = $(this);
    var currentSearchData = {};

    function initPagination(results, itemsPerPage, currentPage) {
        var pages = Math.ceil(results / itemsPerPage);
        $(".pages-top").pagination({
            items: pages,
            cssStyle: 'light-theme',
            prevText: "Предишна",
            nextText: "Следваща",
            currentPage: currentPage,
            onPageClick: function (pageNumber, event) {
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
            onPageClick: function (pageNumber, event) {
                currentSearchData.page = pageNumber;
                performQuery(currentSearchData);
            }
        });
    }

    function generateRangeField(from, to, field) {
        if (from == "")
            from = "*";
        if (to == "")
            to = "*";
        if (field == "year") {
            if (from != "*")
                from = from + "-01-01T00:00:00Z";
            if (to != "*")
                to = to + "-12-31T23:59:59Z";
        }
        return (field + ":[" + from + " TO " + to + "]");
    }

    function generateQueryParams(text) {
        var keywords = container.find("#keywords-field-option").prop("checked");
        var description = container.find("#description-field-option").prop("checked");
        var priceFrom = container.find("#price-field-from").val();
        var priceTo = container.find("#price-field-to").val();
        var distanceFrom = container.find("#distance-field-from").val();
        var distanceTo = container.find("#distance-field-to").val();
        var yearFrom = container.find("#year-field-from").val();
        var yearTo = container.find("#year-field-to").val();
        var highlight = container.find("#highlight-field-option").prop("checked");
        var title = container.find("#title-field-option").prop("checked");
        var all = container.find("#all-field-option").prop("checked");
        var price = generateRangeField(priceFrom, priceTo, "price");
        var year = generateRangeField(yearFrom, yearTo, "year");
        var distance = generateRangeField(distanceFrom, distanceTo, "km");
        var results = container.find("#results-field-from").val();
        if (results <= 0 || results == "")
            results = 15;
        if (text == "")
            text = "*:*";
        var jsonData = {
            "text": text,
            "price": price,
            "keywords": keywords,
            "all": all,
            "results": results,
            "title": title,
            "distance": distance,
            'year': year,
            'highlight': highlight,
            "description": description,
            "page": 1,
            "pageItems": results
        };
        return jsonData;
    }

    function createResultField(id, title, description, keywords, url, price, km, year, highlights) {
        $.each(highlights, function (key, val) {
            if (key == "title")
                title = val[0];
            if (key == "keywords")
                keywords = val[0];
            if (key == "description")
                description = val[0];
        });
        var findMore = $("<a class='find-more' href='#" + id + "'>Намери подобни</a>")
            .click(findNearest);
        var result = $("<div class='search-result'>" +
            "<div class='result-title'><span><i class='fa fa-car' aria-hidden='true'></i>|" + title + "</span></div>" +
            "<div class='result-description'><span><i class='fa fa-file-text-o' aria-hidden='true'></i>|<b>Описание</b>: " + description + "</span></div>" +
            "<div class='result-keywords'><span><i class='fa fa-file-text-o' aria-hidden='true'></i>|<b>Ключови думи:</b> " + keywords + "</span></div>" +
            "<div class='result-price'><span><i class='fa fa-usd' aria-hidden='true'></i>|<b>Цена:</b> " + price + "</span></div>" +
            "<div class='result-year'><span><i class='fa fa-calendar' aria-hidden='true'></i>|<b>Година:</b> " + year + "</span></div>" +
            "<div class='result-distance'><span><i class='fa fa-road' aria-hidden='true'></i>|<b>Километри:</b> " + km + "</span></div>" +
            "<div class='result-visit'><a href='" + url + "'>Научи повече</a></div>" +
            "<div class='nearest-holder' style='display: none'></div>" +
            "</div>" +
            "<div class='result-separator'></div>");
        result.find(".result-visit").prepend(findMore);
        $(".search-results-holder .results-container").append(result);
    }

    function createNearestField(holder, title, description, keywords, url, price, km, year) {
        var result = $("<div class='search-result'>" +
            "<div class='result-title'><span><i class='fa fa-car' aria-hidden='true'></i>|" + title + "</span></div>" +
            "<div class='result-description'><span><i class='fa fa-file-text-o' aria-hidden='true'></i>|<b>Описание</b>: " + description + "</span></div>" +
            "<div class='result-keywords'><span><i class='fa fa-file-text-o' aria-hidden='true'></i>|<b>Ключови думи:</b> " + keywords + "</span></div>" +
            "<div class='result-price'><span><i class='fa fa-usd' aria-hidden='true'></i>|<b>Цена:</b> " + price + "</span></div>" +
            "<div class='result-year'><span><i class='fa fa-calendar' aria-hidden='true'></i>|<b>Година:</b> " + year + "</span></div>" +
            "<div class='result-distance'><span><i class='fa fa-road' aria-hidden='true'></i>|<b>Километри:</b> " + km + "</span></div>" +
            "<div class='result-visit'><a href='" + url + "'>Научи повече</a></div>" +
            "</div>" +
            "<div class='result-separator'></div>");
        holder.find(".nearest-holder").append(result);
    }

    function findNearest() {
        var that = $(this);
        if (that.hasClass("clicked")) {
            if (that.parent().parent().find(".nearest-holder:visible").length <= 0)
                that.text("Скриване на подобни");
            else
                that.text("Покажи подобни");
            that.parent().parent().find(".nearest-holder").slideToggle();
            return;
        }
        var id = that.attr("href").replace("#", "");
        $.ajax({
            url: "services/getNearest",
            type: "GET",
            data: {"id": id},
            dataType: "json"
        }).done(function (result) {
            var docs = $(result);
            that.parent().parent().find(".nearest-holder").hide();

            that.parent().parent().find(".nearest-holder").find("*").remove();
            if (docs.length == 0) {
                that.parent().parent().find(".nearest-holder").append("<div class='search-result-empty'>Няма намерени резултати.</div>");
            }
            docs.each(function (key, val) {
                var id = val.id.toString();
                var title = val.title;
                var description = val.description;
                var keywords = val.keywords;
                var url = val.url;
                var price = val.price + " " + val.currency;
                var km = val.km;
                var year = val.year;
                createNearestField(that.parent().parent(), title, description, keywords, url, price, km, year);
            });
            that.parent().parent().find(".nearest-holder").slideDown();
            that.addClass("clicked");
            that.text("Скриване на подобни");
        });
    }

    function performQuery(parameters) {
        $.ajax({
            url: "services/getResults",
            type: "GET",
            data: parameters,
            dataType: "json"
        }).done(function (result) {
            var docsFound = result.response.numFound;
            var docs = $(result.response.docs);
            var highlights = $(result.highlighting);
            $(".search-results-holder").show();
            $(".search-results-holder .results-container *").remove();
            var results = container.find("#results-field-from").val();
            if (results <= 0 || results == "")
                results = 15;
            initPagination(docsFound, results, parameters.page);
            if (docs.length == 0) {
                $(".search-results-holder .results-container").append("<div class='search-result-empty'>Няма открити резултати.</div>");
            }
            docs.each(function (key, val) {
                var id = val.id.toString();
                var lights = {};
                if (typeof (highlights[0]) != "undefined")
                    lights = highlights[0][id];
                var title = val.title;
                var description = val.description;
                var keywords = val.keywords;
                var url = val.url;
                var price = val.price + " " + val.currency;
                var km = val.km;
                var year = val.year;
                createResultField(id, title, description, keywords, url, price, km, year, lights);
            });
            $(".results-container .search-result").each(function (key, val) {
                sr.reveal($(val), {duration: 400, reset: false});
            })

        });
    }

    var initEvents = function () {
        $(".submit-button").click(function () {
            var text = container.find(".search-text").val();
            var data = generateQueryParams(text);
            currentSearchData = data;
            performQuery(currentSearchData);
        });

        $(".advanced-search-button").click(function () {
            container.find(".advanced-search").slideToggle();
        });

        $(".search-text").keyup(function (e) {
            var code = e.which;
            if (code == 13) e.preventDefault();
            if (code == 32 || code == 13 || code == 188 || code == 186) {
                $(".submit-button").click();
            }
        });
    };

    var buildHtml = function () {
        container.append('<div class="input-holder">' +
            '<div class="input">' +
            '<input type="text" class="search-text"/>' +
            '</div><div class="submit-button">' +
            'Търсене' +
            '</div>' +
            '<div class="advanced-search">' +
            '<div class="advanced-search-section">' +
            '<span class="advanced-search-section-title">' +
            '<i class="fa fa-search-plus" aria-hidden="true"></i>  Tърси в:' +
            '</span>' +
            '<div class="search-option">' +
            '<input type="checkbox" id="all-field-option">' +
            '<label class="noselect" for="all-field-option">' +
            'Всички' +
            '</label>' +
            '</div>' +
            '<div class="search-option" >' +
            '<input type="checkbox" id="title-field-option" >' +
            '<label class="noselect" for="title-field-option">' +
            'Заглавие' +
            '</label>' +
            '</div>' +
            '<div class="search-option">' +
            '<input type="checkbox"  id="keywords-field-option" >' +
            '<label class="noselect" for="keywords-field-option">' +
            'Ключови Думи' +
            '</label>' +
            '</div>' +
            '<div class="search-option">' +
            '<input type="checkbox" id="description-field-option">' +
            '<label class="noselect" for="description-field-option">' +
            'Описание' +
            '</label>' +
            '</div>' +
            '</div>' +
            '<div class="advanced-search-section">' +
            '<span class="advanced-search-section-title">' +
            '<i class="fa fa-usd" aria-hidden="true"></i>  Цена:' +
            '</span>' +
            '<div class="search-option">' +
            '<input type="text" placeholder="От" id="price-field-from">' +
            '<input type="text" placeholder="До" id="price-field-to">' +
            '</div>' +
            '</div>' +
            '<div class="advanced-search-section">' +
            '<span class="advanced-search-section-title">' +
            '<i class="fa fa-road" aria-hidden="true"></i>  Пробег:' +
            '   </span>' +
            '<div class="search-option">' +
            '<input type="text" placeholder="От" id="distance-field-from">' +
            '<input type="text" placeholder="До" id="distance-field-to">' +
            '</div>' +
            '</div>' +
            '<div class="advanced-search-section">' +
            '<span class="advanced-search-section-title">' +
            '<i class="fa fa-calendar" aria-hidden="true"></i>  Година на производство:' +
            '</span>' +
            '<div class="search-option">' +
            '<input type="text" placeholder="От" readonly id="year-field-from">' +
            '<input type="text" placeholder="До" readonly id="year-field-to">' +
            '</div>' +
            '</div>' +
            '<div class="advanced-search-section">' +
            '<span class="advanced-search-section-title">' +
            '<i class="fa fa-cogs" aria-hidden="true"></i>  Други настройки:' +
            '</span>' +
            '<div class="search-option">' +
            '<input type="checkbox" id="highlight-field-option">' +
            '<label class="noselect" for="highlight-field-option">' +
            'Оцветяване' +
            '</label>' +
            '</div>' +
            '</div>' +
            '<div class="advanced-search-section">' +
            '<span class="advanced-search-section-title">' +
            '<i class="fa fa-sort-numeric-asc" aria-hidden="true"></i>  Брой резултати на страница:' +
            '</span>' +
            '<div class="search-option">' +
            '<input type="text" placeholder="15" id="results-field-from">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="advanced-search-button">' +
            'Настройки за търсене' +
            '</div>' +
            '</div>' +
            '<div class="search-results-holder">' +
            '<div class="pagination">' +
            '<div class="paging-container-top">' +
            '<div class="pages-top">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="results-container">' +
            '</div>' +
            '<div class="pagination">' +
            '<div class="paging-container-bottom">' +
            '<div class="pages-bottom">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>');
        $(container).find(".search-option input").iCheck({
            labelHover: false,
            cursor: true,
            checkboxClass: 'icheckbox_flat-blue',
            radioClass: 'iradio_flat-blue'
        }).on('ifToggled', function(event){
            var target = $(event.target);
            var checked = target.prop("checked");

            if(target.attr("id") == "all-field-option"){
                if(target.attr("id") !== "highlight-field-option") {
                    container.find("#keywords-field-option")
                        .prop("checked", false)
                        .iCheck('update');
                    container.find("#description-field-option")
                        .prop("checked", false)
                        .iCheck('update');
                    container.find("#title-field-option")
                        .prop("checked", false)
                        .iCheck('update');
                }
            }else {
                if (target.attr("id") !== "highlight-field-option") {
                    container.find("#all-field-option")
                        .prop("checked", false)
                        .iCheck('update');
                }
            }
            $(this).iCheck('update');
        });
        $("#all-field-option").prop("checked", true);
        $("#all-field-option").iCheck('update');
        container.find("#year-field-from,#year-field-to").datepicker({
            format: "YYYY"
        });
    };
    buildHtml();
    initEvents();
};