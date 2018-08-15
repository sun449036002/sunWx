//HTML反转义
function htmlDecode(text) {
    var temp = document.createElement("div");
    temp.innerHTML = text;
    var output = temp.innerText || temp.textContent;
    temp = null;
    return output;
}

/**
 *
 */
function getRoomList(data, callback) {
    if (typeof data !== "object") {
        data= {}
    }

    var isFirstPage = !data.page || data.page === 1;
    if (isFirstPage) {
        //第一页时，先清空原有数据
        $(".house-list a").remove();
    }

    $.getJSON("/room/getRoomList", data, function (res){
        var html = "";
        var list = res.data.list || [];
        if (list.length > 0) {
            $.each(list, function(k, item){
                // console.log(item);
                html = '<a href="/room/detail?id=' + item.id + '">' +
                    '<div class="item">' +
                        '<div class="cover" style="background-image: url(\'' + item.cover + '\')"></div>' +
                        '<div class="info">' +
                            '<div class="name">' + item.name + '</div>' +
                            '<div class="area">' + item.area + '</div>' +
                            '<div class="categoryName">' + item.categoryName + '</div>' +
                            '<div class="avg-price">均价：' + item.avgPrice + '元/m²</div>' +
                            '<div class="avg-price">总价：' + (item.totalPrice === null ? 0 : item.totalPrice) + '万元</div>' +
                            '<div class="btn-see-house">预约看房</div>' +
                        '</div>' +
                    '</div>' +
                    '</a>';
                $(".house-list .loading").before(html);
            });

            if (typeof callback === 'function') {
                callback({isEnd : res.data.isEnd});
            }
        } else {
            //第一页无数据，显示无内容提示
            if (isFirstPage) {
                var noDataDom = $(".house-list .no-data");
                if(noDataDom.length > 0) noDataDom.show();
            }
            if (typeof callback === 'function') {
                callback({isEnd : 1, from : "empty list"});
            }
        }
    })
}