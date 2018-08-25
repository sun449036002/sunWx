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

    var noDataDom = $(".house-list .no-data");
    var isFirstPage = !data.page || data.page === 1;
    if (isFirstPage) {
        //第一页时，先清空原有数据
        $(".house-list a").remove();
        if(noDataDom.length > 0) noDataDom.hide();
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
                            '<div class="btn-see-house" data-id="' + item.id + '">预约看房</div>' +
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
                if(noDataDom.length > 0) noDataDom.show();
            }
            if (typeof callback === 'function') {
                callback({isEnd : 1, from : "empty list"});
            }
        }
    })
}

//解决 微信浏览器PC端不支持Object.assign方法
if (typeof Object.assign != 'function') {
    // Must be writable: true, enumerable: false, configurable: true
    Object.defineProperty(Object, "assign", {
        value: function assign(target, varArgs) { // .length of function is 2
            'use strict';
            if (target === null) { // TypeError if undefined or null
                throw new TypeError('Cannot convert undefined or null to object');
            }

            var to = Object(target);

            for (var index = 1; index < arguments.length; index++) {
                var nextSource = arguments[index];

                if (nextSource !== null) { // Skip over if undefined or null
                    for (var nextKey in nextSource) {
                        // Avoid bugs when hasOwnProperty is shadowed
                        if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                            to[nextKey] = nextSource[nextKey];
                        }
                    }
                }
            }
            return to;
        },
        writable: true,
        configurable: true
    });
}

/**
 * 动画效果，从大到小的缩放
 * @param target
 * @param bigW
 * @param smallW
 * @param bigH
 * @param smallH
 */
function animateBigSmall(target, bigW, smallW, bigH, smallH) {
    $(target).animate({
        width:bigW,
        height : bigH

    }, {
        easing: "easeOutBounce",
        duration: 500,
        complete:function(){
            $(target).animate({
                width:smallW,
                height : smallH
            }, {
                easing: "easeOutBounce",
                duration: 500,
                complete:function(){}
            });
        }
    });
}