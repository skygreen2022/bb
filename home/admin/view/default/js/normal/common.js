/**
 * Created by skygreen on 2017/3/13
 */
var commonLibrary = {
    url_province: '/address/selectProvinces',
    url_city    : '/address/selectCitys',
    url_district: '/address/selectDistricts',
    url_img_crossdomain: 'api/common/crossdominimg.php?src=',
    init        : function(){
        this.bootboxInit();
    },
    autoresize  : function(){
        var bc_line_height = 0;
        if ($(".breadcrumb-line").height()) bc_line_height = $(".breadcrumb-line").height();
        var content_margin_top = parseInt($(".content-wrapper .container-fluid").css("margin-top"));
        var offset = $(window).height() - $(".navbar-container").height() - bc_line_height - $("footer").height() - content_margin_top;
        // if (offset>440 || $(window).height()<525) $(".content-wrapper .container-fluid").css("height", offset);
        $(".content-wrapper .container-fluid").removeAttr("style");

        if (($(".content-wrapper .container-fluid").height()<625)||($("body").height()<$(window).height())) {
            $(".content-wrapper .container-fluid").css("height", offset+1);
        }
        if ( $(window).width() > 752 ) $(".sidebar").removeAttr("style");
        if ( $(window).width() < 992 ){
          $(".navbar .navbar-container").removeClass("container");
          $(".page-container").removeClass("container");
          $("#btn-layout-small").css("display","none");
        }else{
          $("#btn-layout-small").css("display","block");
        }
    },
    dropdown    : function(){
        //Adding a slide effect to bootstrap dropdown
        //Add slideDown animation to Bootstrap dropdown when expanding.
        $('.dropdown').on('show.bs.dropdown', function() {
          $(this).find('.dropdown-menu').first().stop(true, true).slideDown();
        });

        //Add slideUp animation to Bootstrap dropdown when collapsing.
        $('.dropdown').on('hide.bs.dropdown', function() {
          $(this).find('.dropdown-menu').first().stop(true, true).slideUp();
        });

        $('.navbar .dropdown').hover(function() {
          $(this).find('.dropdown-menu').first().stop(true, true).delay(250).slideDown();
        }, function() {
          $(this).find('.dropdown-menu').first().stop(true, true).delay(100).slideUp()
        });
    },
    bootboxInit : function(){
        if (bootbox) {
            bootbox.addLocale('zh_CN_OK', { OK : '??????', CANCEL  : "??????", CONFIRM : "??????" });
            bootbox.setLocale("zh_CN_OK");
            bootbox.setDefaults({
                "title"  : "????????????",
                "animate": true
            });
        }
    },
    param       : function(name){
        var results = new RegExp('[\?&]' + name + '=([^]*)').exec(window.location.href);
        if (results==null){
           return null;
        } else {
           return results[1] || 0;
        }
    },
    //???????????????????????????????????????????????????https??????????????????????????????????????????????????????????????????????????????
    //?????????????????????: https://github.com/skygreen2001/betterlife.core
    remoteImgShow: function(imgContainer){
        var ctrl = this;
        $(imgContainer+" img").each(function(){
            var img_src = $(this).attr("src");
            if (img_src.indexOf("http://") > -1){
                $(this).attr("src", ctrl.url_img_crossdomain+img_src);
            }
        });
    },
    /** JSON??????POST?????? */
    post: function(url,data,func) {
        $.ajax({
            type:'POST',
            url:url,
            data:JSON.stringify(data),
            dataType:'JSON',
            contentType:'application/json;charset=utf-8',
            success:func
        })
    },
    /** JSON??????POST?????? */
    get: function (url,data,func) {
        $.ajax({
            url:url,
            type:'GET',
            async:'true',
            data:data,
            dataType:'JSON',
            contentType:'application/json;charset=utf-8',
            success:func
        });
    },
    /** ?????????????????? */
    showAddress: function(domain, param_data){
       this.selectProvinces(domain, param_data);
       this.selectCity(domain, param_data);
       this.selectDistrict(domain, param_data);
    },
    // ????????????
    selectProvinces: function(domain, param_data){
        var proviceId      = domain + " " + param_data.province.id;
        var proviceDefault = param_data.province.value;
        $.ajax({
            type: "GET",
            url: this.url_province,
            success: function(res){
                //  console.log(res);
                var list = res.result.countryAddress;
                $(proviceId).empty();//????????????select??????????????????
                var selText = " selected='selected' ";
                if (proviceDefault <= 0) {
                    $(proviceId).append("<option " + selText + " value=''>?????????</option>");
                } else {
                    $(proviceId).append("<option value=''>?????????</option>");
                }
                for (var i = 0; i < list.length; i++) {
                    var item = list[i];
                    if (proviceDefault == item.id){
                        $(proviceId).append("<option " + selText + " value=" + item.id + ">" + item.name + "</option>");
                    }else {
                        $(proviceId).append("<option value=" + item.id + ">" + item.name + "</option>");
                    }
                }
            }
        });
    },
    // ?????????
    selectCity: function(domain, param_data){
        var proviceId  = domain + " " + param_data.province.id;
        var cityId     = domain + " " + param_data.city.id;
        var districtId = domain + " " + param_data.district.id;

        var proviceDefault = param_data.province.value;
        var cityDefault    = param_data.city.value;

        if (cityDefault > 0) {
            var data = {"id": proviceDefault};
            $.ajax({
                type       : "POST",
                url        : this.url_city,
                data       : JSON.stringify(data),
                contentType:'application/json;charset=utf-8',
                success    : function(res){
                    //  console.log(res);
                    var list = res.result.countryAddress;
                    $(cityId).empty();//????????????select??????????????????
                    $(cityId).append("<option value=''>?????????</option>");
                    for (var i = 0; i < list.length; i++) {
                        var item = list[i];
                        if (cityDefault == item.id) {
                            $(cityId).append("<option selected='selected' value=" + item.id + ">" + item.name + "</option>");
                        } else {
                            $(cityId).append("<option value=" + item.id + ">" + item.name + "</option>");
                        }
                    }
                }
            });
        } else {
            $(cityId).append("<option selected='selected' value=''>?????????</option>");
        }

        var ctrl = this;
        $(proviceId).change(function(){//?????????
           var data = {"id":""}
           data.id = $(this).children('option:selected').val();
           $.ajax({
               type: "POST",
               url: ctrl.url_city,
               data: JSON.stringify(data),
               contentType:'application/json;charset=utf-8',
               success: function(res){
                  //  console.log(res);
                  var list = res.result.countryAddress;
                  $(cityId).empty();//????????????select??????????????????
                  $(districtId).empty();//????????????select??????????????????

                  for (var i = 0; i < list.length; i++) {
                      var item = list[i];
                      $(cityId).append("<option value=" + item.id + ">" + item.name + "</option>");
                  }
                  var data = {"id":""}
                  data.id = $(cityId).children('option:selected').val();
                  if (data.id>0) {
                      $.ajax({
                          type: "POST",
                          url : ctrl.url_district,
                          data: JSON.stringify(data),
                          contentType:'application/json;charset=utf-8',
                          success: function(res){
                              // console.log(res);
                              var list = res.result.countryAddress;
                              $(districtId).empty();//????????????select??????????????????
                              for (var i = 0; i < list.length; i++) {
                                  var item = list[i];
                                  $(districtId).append("<option value=" + item.id + ">" + item.name + "</option>");
                              }
                          }
                      });
                  }else{
                      $(districtId).html("");
                  }
               }
            });
        });
    },
    // ????????????
    selectDistrict:function(domain, param_data){
        var proviceId  = domain + " " + param_data.province.id;
        var cityId     = domain + " " + param_data.city.id;
        var districtId = domain + " " + param_data.district.id;
        var cityDefault = param_data.city.value;
        var districtDefault = param_data.district.value;

        if (cityDefault > 0){
            var data = {"id": cityDefault};
            $.ajax({
                type: "POST",
                url : this.url_district,
                data: JSON.stringify(data),
                contentType:'application/json;charset=utf-8',
                success: function(res){
                    //  console.log(res);
                    var list = res.result.countryAddress;
                    $(districtId).empty();//????????????select??????????????????
                    for (var i = 0; i < list.length; i++) {
                        var item = list[i];
                        if (districtDefault == item.id){
                            $(districtId).append("<option selected='selected' value=" + item.id + ">" + item.name + "</option>");
                        }else {
                            $(districtId).append("<option  value=" + item.id + ">" + item.name + "</option>");
                        }
                    }
                }
            });
        } else {
            $(districtId).append("<option selected='selected' value=''>?????????</option>");
        }

        var ctrl = this;
        $(cityId).change(function(){//????????????
           var data = {"id":""}
           data.id = $(this).children('option:selected').val();
           if (data.id>0) {
               $.ajax({
                   type: "POST",
                   url : ctrl.url_district,
                   data: JSON.stringify(data),
                   contentType:'application/json;charset=utf-8',
                   success: function(res){
                      //  console.log(res);
                       var list = res.result.countryAddress;
                       $(districtId).empty();//????????????select??????????????????

                       for (var i = 0; i < list.length; i++) {
                            var item = list[i];
                            $(districtId).append("<option  value=" + item.id + ">" + item.name + "</option>");
                       }
                   }
                });
           }else{
               $(districtId).html("");
           }
        });
    }
}

$(function(){
    $.extend({"common":commonLibrary});
    $.common.init();
});
