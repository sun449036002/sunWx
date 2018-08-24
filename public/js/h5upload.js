//图片压缩处理
var hi=0;

; (function () {
    /**
     * 加载的时候进行抽样检测
     * 在iOS中,大于2M的图片会抽样渲染
     */
    function detectSubsampling(img) {
        var iw = img.naturalWidth, ih = img.naturalHeight;
        if (iw * ih > 1024 * 1024) { //大于百万像素的就会进行抽样
            var canvas = document.createElement('canvas');
            canvas.width = canvas.height = 1;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(img, -iw + 1, 0);
            // subsampled image becomes half smaller in rendering size.
            // check alpha channel value to confirm image is covering edge pixel or not.
            // if alpha value is 0 image is not covering, hence subsampled.
            return ctx.getImageData(0, 0, 1, 1).data[3] === 0;
        } else {
            return false;
        }
    }

    /**
     * Detecting vertical squash in loaded image.
     * Fixes a bug which squash image vertically while drawing into canvas for some images.
     */
    function detectVerticalSquash(img, iw, ih) {
        var canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = ih;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        var data = ctx.getImageData(0, 0, 1, ih).data;
        // search image edge pixel position in case it is squashed vertically.
        var sy = 0;
        var ey = ih;
        var py = ih;
        while (py > sy) {
            var alpha = data[(py - 1) * 4 + 3];
            if (alpha === 0) {
                ey = py;
            } else {
                sy = py;
            }
            py = (ey + sy) >> 1;
        }
        var ratio = (py / ih);
        return (ratio === 0) ? 1 : ratio;
    }

    /**
     * Rendering image element (with resizing) and get its data URL
     */
    function renderImageToDataURL(img, options, doSquash) {
        var canvas = document.createElement('canvas');
        renderImageToCanvas(img, canvas, options, doSquash);
        return canvas.toDataURL("image/jpeg", options.quality || 0.8);
    }

    /**
     * Rendering image element (with resizing) into the canvas element
     */
    function renderImageToCanvas(img, canvas, options, doSquash) {
        var iw = img.naturalWidth, ih = img.naturalHeight;
        if (!(iw + ih)) return;
        var width = options.width, height = options.height;
        var ctx = canvas.getContext('2d');
        ctx.save();
        transformCoordinate(canvas, ctx, width, height, options.orientation);
        var subsampled = detectSubsampling(img);
        if (subsampled) {
            iw /= 2;
            ih /= 2;
        }
        var d = 1024; // size of tiling canvas
        var tmpCanvas = document.createElement('canvas');
        tmpCanvas.width = tmpCanvas.height = d;
        var tmpCtx = tmpCanvas.getContext('2d');
        var vertSquashRatio = doSquash ? detectVerticalSquash(img, iw, ih) : 1;
        var dw = Math.ceil(d * width / iw);
        var dh = Math.ceil(d * height / ih / vertSquashRatio);
        var sy = 0;
        var dy = 0;
        while (sy < ih) {
            var sx = 0;
            var dx = 0;
            while (sx < iw) {
                tmpCtx.clearRect(0, 0, d, d);
                tmpCtx.drawImage(img, -sx, -sy);
                ctx.drawImage(tmpCanvas, 0, 0, d, d, dx, dy, dw, dh);
                sx += d;
                dx += dw;
            }
            sy += d;
            dy += dh;
        }
        ctx.restore();
        tmpCanvas = tmpCtx = null;
    }

    /**
     * Transform canvas coordination according to specified frame size and orientation
     * 根据帧的大小和方向调整canvas
     * 方向值是来自EXIF标签
     */
    function transformCoordinate(canvas, ctx, width, height, orientation) {
        switch (orientation) {
            case 5:
            case 6:
            case 7:
            case 8:
                canvas.width = height;
                canvas.height = width;
                break;
            default:
                canvas.width = width;
                canvas.height = height;
        }
        switch (orientation) {
            case 2:
                // horizontal flip
                ctx.translate(width, 0);
                ctx.scale(-1, 1);
                break;
            case 3:
                // 180 rotate left
                ctx.translate(width, height);
                ctx.rotate(Math.PI);
                break;
            case 4:
                // vertical flip
                ctx.translate(0, height);
                ctx.scale(1, -1);
                break;
            case 5:
                // vertical flip + 90 rotate right
                ctx.rotate(0.5 * Math.PI);
                ctx.scale(1, -1);
                break;
            case 6:
                // 90 rotate right
                ctx.rotate(0.5 * Math.PI);
                ctx.translate(0, -height);
                break;
            case 7:
                // horizontal flip + 90 rotate right
                ctx.rotate(0.5 * Math.PI);
                ctx.translate(width, -height);
                ctx.scale(-1, 1);
                break;
            case 8:
                // 90 rotate left
                ctx.rotate(-0.5 * Math.PI);
                ctx.translate(-width, 0);
                break;
            default:
                break;
        }
    }

    var URL = window.URL && window.URL.createObjectURL ? window.URL :
              window.webkitURL && window.webkitURL.createObjectURL ? window.webkitURL :
              null;

    /**
     * MegaPixImage class
     */
    function MegaPixImage(srcImage) {
        if (window.Blob && srcImage instanceof Blob) {
            if (!URL) { throw Error("No createObjectURL function found to create blob url"); }
            var img = new Image();
            img.src = URL.createObjectURL(srcImage);
            this.blob = srcImage;
            srcImage = img;
        }
        if (!srcImage.naturalWidth && !srcImage.naturalHeight) {
            var _this = this;
            srcImage.onload = srcImage.onerror = function () {
                var listeners = _this.imageLoadListeners;
                if (listeners) {
                    _this.imageLoadListeners = null;
                    for (var i = 0, len = listeners.length; i < len; i++) {
                        listeners[i]();
                    }
                }
            };
            this.imageLoadListeners = [];
        }
        this.srcImage = srcImage;
    }

    /**
     * Rendering megapix image into specified target element
     */
    MegaPixImage.prototype.render = function (target, options, callback) {
        if (this.imageLoadListeners) {
            var _this = this;
            this.imageLoadListeners.push(function () { _this.render(target, options, callback); });
            return;
        }
        options = options || {};
        var imgWidth = this.srcImage.naturalWidth, imgHeight = this.srcImage.naturalHeight,
            width = options.width, height = options.height,
            maxWidth = options.maxWidth, maxHeight = options.maxHeight,
            doSquash = !this.blob || this.blob.type === 'image/jpeg';
        if (width && !height) {
            height = (imgHeight * width / imgWidth) << 0;
        } else if (height && !width) {
            width = (imgWidth * height / imgHeight) << 0;
        } else {
            width = imgWidth;
            height = imgHeight;
        }
        if (maxWidth && width > maxWidth) {
            width = maxWidth;
            height = (imgHeight * width / imgWidth) << 0;
        }
        if (maxHeight && height > maxHeight) {
            height = maxHeight;
            width = (imgWidth * height / imgHeight) << 0;
        }
        var opt = { width: width, height: height };
        for (var k in options) opt[k] = options[k];
        target.tagName = target.tagName || "IMG";
        var tagName = target.tagName.toLowerCase();
        if (tagName === 'img') {
            target.src = renderImageToDataURL(this.srcImage, opt, doSquash);
        } else if (tagName === 'canvas') {
            renderImageToCanvas(this.srcImage, target, opt, doSquash);
        }
        if (typeof this.onrender === 'function') {
            this.onrender(target);
        }
        if (callback) {
            callback();
        }
        if (this.blob) {
            this.blob = null;
            URL.revokeObjectURL(this.srcImage.src);
        }
    };

    /**
     * Export class to global
     */
    if (typeof define === 'function' && define.amd) {
        define([], function () { return MegaPixImage; }); // for AMD loader
    } else if (typeof exports === 'object') {
        module.exports = MegaPixImage; // for CommonJS
    } else {
        this.MegaPixImage = MegaPixImage;
    }

})();

; (function ($) {
    $.extend($, {
        fileUpload: function (options) {
            var para = {
                multiple: true,
                filebutton: ".filePicker",
                uploadButton: null,
                url: "/img/upload",
                base64strUrl: "/img/upload",
                filebase: "mfile", //mvc后台需要对应的名称
                auto: true,
                previewZoom: null,
                uploadButtonName: null,
                fileInfoId: null,
                uploadComplete: null,
                uploadError: function (err) {
                    console.log("uploadError", err);
                },
                onProgress: function (percent) {  // 提供给外部获取单个文件的上传进度，供外部实现上传进度效果
                    console.log(percent);
                    $('#show-status').text('文件已上传：' + percent);
                    if (percent == '100%') {
                        setTimeout(function () {
                            $('#show-status').text('文件上传完毕');
                            setTimeout(function () {
                                if ($('#show-status').text() && $('#show-status').text().indexOf('上传完毕') != -1) {
                                    $('#show-status').empty();
                                    $('#show-status').get(0).id = "";
                                }
                            }, 600);
                        }, 600);                  
                    }
                },
            };
            para = $.extend(para, options);

            var $self = $(para.filebutton);
            //先加入一个file元素
            var multiple = "";  // 设置多选的参数
            para.multiple ? multiple = "multiple" : multiple = "";


            $self.css('position', 'relative');
            $self.append('<input id="'+para.uploadButtonName+'"  style="opacity:0;position:absolute;top: 0;left: 0;width:100%;height:100%" accept="image/jpg,image/jpeg,image/png"  type="file" size="30" name="'+para.uploadButtonName+'[]" ' + multiple + '>');

            var doms = {
                "fileToUpload": $self.find(para.uploadButton)
                // "thumb": $self.find(".thumb"),
                // "progress": $self.find(".upload-img.html-progress")
            };

            function getBase64Image(img) {
                var canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;

                var ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, img.width, img.height);

                var dataURL = canvas.toDataURL("image/jpeg", 1.0);

                //$('body').append('dataURL:' + dataURL);
                //$('body').append('<br />');
                
                // 当获取不值时，使用img.src
                if (!dataURL || dataURL.replace('data:,', '') == '') {
                    dataURL = img.src;
                }
                //$('body').append('<hr />');
                //$('body').append('dataURL:' + dataURL);
                //$('body').append('<br />');

                //$('body').append(dataURL);

                return dataURL;

                // return dataURL.replace("data:image/png;base64,", "");
            }
            function simpleSize(size) {
                if (!size) return "0";
                if (size < 1024) {
                    return size;
                }
                var kb = size / 1024;
                if (kb < 1024) {
                    return kb.toFixed(2) + "K";
                }
                var mb = kb / 1024;
                if (mb < 1024) {
                    return mb.toFixed(2) + "M";

                }
                var gb = mb / 1024;
                return gb.toFixed(2) + "G";
            };
            //Convert DataURL to Blob to send over Ajax
            function dataURItoBlob(dataUrl) {
                // convert base64 to raw binary data held in a string
                // doesn't handle URLEncoded DataURIs - see SO answer #6850276 for code that does this
                var byteString = atob(dataUrl.split(',')[1]);

                // separate out the mime component
                var mimeString = dataUrl.split(',')[0].split(':')[1].split(';')[0];

                // write the bytes of the string to an ArrayBuffer
                var ab = new ArrayBuffer(byteString.length);
                var ia = new Uint8Array(ab);
                for (var i = 0; i < byteString.length; i++) {
                    ia[i] = byteString.charCodeAt(i);
                }
                return new Blob([ab], { type: 'image/jpeg' });
            }

            var uploadCount = 0;
            var core = {
                fileSelected: function () {
                    var files = $(para.uploadButton)[0].files;
                    var count = files.length;
                    console.log("共有" + count + "个文件");
					//$('#show-status1').append("共有" + count + "个文件");
					try{
                    for (var i = 0; i < count; i++) {
                        var item = files[i];
                        console.log("原图片大小", item.size);
                        $('#show-status').text('原图片大小：' + (parseFloat(item.size)/1024/1024));
                        if (item.size > 1024 * 1024) {
                            console.log("图片大于1M，开始进行压缩...");
                            $('#show-status').text('图片大于1M，开始进行压缩...');

                            var img=new Image();
                            var canvas = document.createElement("canvas");
                            var ctx = canvas.getContext('2d');
                            var reader = new FileReader();
                            reader.onload = function() {
                                var url = reader.result;		//读取到的文件内容.这个属性只在读取操作完成之后才有效,并且数据的格式取决于读取操作是由哪个方法发起的.所以必须使用reader.onload，
                                img.src=url;		//reader读取的文件内容是base64,利用这个url就能实现上传前预览图片
                            };

                            img.onload = function() {
                                var w = img.naturalWidth,
                                    h = img.naturalHeight;
                                if(w>500){
                                    w=500;
                                    h=w*0.75;
                                }
                                canvas.width = w;
                                canvas.height = h;
                                ctx.drawImage(img, 0, 0, w, h, 0, 0, w, h);

                                var base64 = getBase64Image(img);
                                if (para.auto) core.uploadBase64str(base64);
                            };
                            reader.readAsDataURL(item);
                        } else {
                            if (para.auto){
								core.uploadFile(item);
							} 
                        }
                        core.previewImage(item);
                    }
					}catch(error){
						alert(error);
					}
                },
                uploadBase64str: function (base64Str) {

                    //var blob = dataURItoBlob(base64Str);
                    //console.log("压缩后的文件大小", blob.size);
                    //core.uploadFile(blob);
                    var formdata = new FormData();
                    formdata.append("base64str", base64Str);
                    var xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (e) {
                        var percentComplete = Math.round(e.loaded * 100 / e.total);
                        para.onProgress(percentComplete.toString() + '%');
                    });
                    xhr.addEventListener("load", function (e) {
                        para.uploadComplete(xhr.responseText);
                    });
                    xhr.addEventListener("error", function (e) {
                        para.uploadError(e);
                    });
                    //$('#show-status1').append(base64Str.length+',');
                    xhr.open("post", para.base64strUrl, true);
                    xhr.setRequestHeader("X-CSRF-TOKEN", para.csrf_token);
                    xhr.send(formdata);
                },
                uploadFile: function (file) {
                    console.log("开始上传");
                    $('#show-status').text('开始上传');
                    var formdata = new FormData();

                    formdata.append(para.filebase, file);//这个名字要和mvc后台配合

                    var xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (e) {

                        var percentComplete = Math.round(e.loaded * 100 / e.total);
                        para.onProgress(percentComplete.toString() + '%');
                    });
                    xhr.addEventListener("load", function (e) {
                        para.uploadComplete(xhr.responseText);
                    });
                    xhr.addEventListener("error", function (e) {
                        para.uploadError(e);
                    });
					//$('#show-status1').append('b ');
                    xhr.open("post", para.url, true);
                    xhr.setRequestHeader("X-CSRF-TOKEN", para.csrf_token);
                    xhr.send(formdata);
                },
                checkComplete:function() {
                    var all = (doms.fileToUpload)[0].files.length;
                    if (all == uploadCount) {
                        //console.log(all + "个文件上传完毕");
                        $('#show-status').text('文件上传完毕');
                        setTimeout(function () {
                            if ($('#show-status').text() && $('#show-status').text().indexOf('上传完毕') != -1) {
                                $('#show-status').empty();
                            }
                        }, 600);
                        doms.fileToUpload.remove();
                        //input有一个问题就是选择重复的文件不会触发change事件，所以做了一个处理，再每次上传完之后删掉这个元素再新增一个input。
                        $self.append('<input id="'+para.uploadButtonName+'"  style="opacity:0;position:absolute;top: 0;left: 0;width:100%;height:100%" accept="image/jpg,image/jpeg,image/png"  type="file" size="30" name="'+para.uploadButtonName+'[]" ' + multiple + '>');

                    }
                },
                uploadFiles: function () {
                    var files = (doms.fileToUpload)[0].files;
                    for (var i = 0; i < files.length; i++) {
                        core.uploadFile(files[i]);
                    }
                },
                previewImage: function (file) {
                    if (!para.previewZoom) return;
                    var img = document.createElement("img");
                    var div = document.createElement("div");
                    div.id = "show-status";
                    var imgdiv = document.createElement("div");
                    imgdiv.appendChild(img);
                    imgdiv.appendChild(div);
                    img.file = file;
                    $(para.previewZoom).append(imgdiv);
                    // 使用FileReader方法显示图片内容
                    var reader = new FileReader();
                    reader.onload = (function (aImg) {
                        return function (e) {
                            aImg.src = e.target.result;
                        };
                    })(img);
                    reader.readAsDataURL(file);
                }
            }

            $(document).on("change", para.uploadButton, function () {
                core.fileSelected();
            });

            $(document).on("click", para.filebutton, function () {
                console.log("clicked");
            });
//          if (para.uploadButton) {
//              $(document).on("click", para.uploadButton, function () {
//                  core.uploadFiles();
//              });
//          }
            
        }
    });
})(Zepto);



			
			