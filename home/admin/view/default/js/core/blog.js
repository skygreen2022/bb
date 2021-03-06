$(function(){
    //Datatables中文网[帮助]: http://datatables.club/
    if ($.dataTable) {
        var infoTable = $('#infoTable').DataTable({
            "language"  : $.dataTable.chinese,
            "processing": true,
            "serverSide": true,
            "retrieve"  : true,
            "ajax": {
                "url" : "api/web/list/blog.php",
                "data": function ( d ) {
                    d.query    = $("#input-search").val();
                    d.pageSize = d.length;
                    d.page     = d.start / d.length + 1;
                    d.limit    = d.start + d.length;
                    return d;
                },
                //可以对返回的结果进行改写
                "dataFilter": function(data){
                    return data;
                }
            },
            "responsive"   : true,
            "searching"    : false,
            "ordering"     : false,
            "dom"          : '<"top">rt<"bottom"ilp><"clear">',
            "deferRender"  : true,
            "bStateSave"   : true,
            "bLengthChange": true,
            "aLengthMenu"  : [[10, 25, 50, 100,-1],[10, 25, 50, 100,'全部']],
            "columns": [
                { data:"blog_name" },
                { data:"username" },
                { data:"icon_url" },
                { data:"isPublic" },
                { data:"status" },
                { data:"publish_date"},
                { data:"blog_id" }
            ],
            "columnDefs": [
                {"orderable": false, "targets": 2,
                 "render"   : function(data, type, row) {
                    // 该图片仅供测试
                    if ($_.params("d")=="1") data = "home/admin/view/default/resources/images/beauty.jpg";
                    if ( !data ) data = "home/admin/view/default/resources/images/beauty.jpg";
                    var blog_id = row.blog_id;
                    var result = '<a id="' + "imgUrl" + blog_id + '" href="#"><img src="' + data + '" class="img-thumbnail" alt="' + row.blog_name + '" /></a>';

                    $("body").off('click', 'a#imgUrl' + blog_id);
                    $("body").on('click', 'a#imgUrl' + blog_id, function(){
                        var imgLink = $('a#imgUrl' + blog_id + " img").attr('src');
                        $('#imagePreview').attr('src', imgLink);
                        $('#imagePreview-link').attr('href', imgLink);
                        var isShow = $.dataTable.showImages($(this).find("img"), "#imageModal .modal-dialog");
                        if (isShow) $('#imageModal').modal('show'); else window.open(imgLink, '_blank');
                    });
                    return result;
                 }
                },
                {"orderable": false, "targets": 3,
                 "render"   : function(data,type,row){
                    if ( data == 1 ) {
                        return '是';
                    } else {
                        return '否';
                    }
                 }
                },
                {"orderable": false, "targets": 4,
                 "render"   : function(data, type, row){
                    switch (data) {
                      case '0':
                        return '<span class="status-wait">待审核</span>';
                        break;
                      case '1':
                        return '<span class="status-pass">正常</span>';
                        break;
                      default:
                        return '<span class="status-fail">已结束</span>';
                    }
                 }
                },
                {"orderable": false, "targets": 6,
                 "render"   : function(data, type, row){
                    var result = $.templates("#actionTmpl").render({ "id"  : data });

                    $("body").off('click', 'a#info-view' + data);
                    $("body").on('click', "a#info-view"+data, function(){
                        location.href = 'index.php?go=admin.blog.view&id='+data;
                    });

                    $("body").off('click', 'a#info-edit' + data);
                    $("body").on('click', "a#info-edit"+data, function(){
                        location.href = 'index.php?go=admin.blog.edit&id='+data;
                    });

                    $("body").off('click', 'a#info-dele' + data);
                    $("body").on('click', 'a#info-dele' + data, function(){//删除
                        bootbox.confirm("确定要删除该博客:" + data + "?",function(result){
                            if ( result == true ){
                                $.get("index.php?go=admin.blog.delete&id="+data, function(response, status){
                                    $( 'a#info-dele' + data ).parent().parent().css("display", "none");
                                });
                            }
                        });
                    });
                    return result;
                }
             }
            ],
            "initComplete":function(){
                $.dataTable.filterDisplay();
            },
            "drawCallback": function( settings ) {
                $.dataTable.pageNumDisplay(this);
                $.dataTable.filterDisplay();
            }
        });
        $.dataTable.doFilter(infoTable);

        $("#btn-blog-import").click(function(){
            $("#upload_file").trigger('click');
        });

        $("#upload_file").change(function(){
            var data = new FormData();
            data.append('upload_file', $("#upload_file").get(0).files[0]);
            $.ajax({
                type: 'POST',
                url: "index.php?go=admin.blog.import",
                data: data,
                success: function(response) {
                    if (response && response.success){
                        bootbox.alert("导入博客成功！");
                        infoTable.draw();
                    } else {
                        bootbox.alert("导入博客失败！");
                    }
                    $("#upload_file").val("");

                },
                error: function(response) {
                    $("#upload_file").val("");
                },
                processData: false,
                contentType: false,
                dataType   : "json"
            });
        });

        $("#btn-blog-export").click(function(){
            var query = $("#input-search").val();
            $.getJSON("index.php?go=admin.blog.export&query=" + query, function(response){
                window.open(response.data);
            });
        });
    }

    if( $(".content-wrapper .edit form").length ){
        $.edit.fileBrowser("#iconImage", "#iconImageTxt", "#iconImageDiv");
        $.edit.datetimePicker('#publish_date');
        $.edit.select2('#category_id', "", select_category);
        $.edit.select2('#tags_id', "api/web/select/tags.php", select_tags);
        $.edit.select2("#status", "api/web/data/blogStatus.json", select_status);

        $("input[name='isPublic']").bootstrapSwitch();

        $('input[name="isPublic"]').on('switchChange.bootstrapSwitch', function(event, state) {
            console.log(state);
        });

        $('#editBlogForm').validate({
            errorElement: 'div',
            errorClass: 'help-block',
            // focusInvalid: false,
            focusInvalid: true,
            // debug:true,
            rules: {
                blog_name: {
                    required: true
                },
                sequenceNo: {
                    required: true,
                    number: true
                }
            },
            messages: {
                blog_name:"此项为必填项",
                sequenceNo:{
                    required:"此项为必填项",
                    number:"此项必须为数字"
                }
            },
            invalidHandler: function (event, validator) { //display error alert on form submit
                $('.alert-danger', $('.login-form')).show();
            },
            highlight: function (e) {
                $(e).closest('.form-group').removeClass('has-info').addClass('has-error');
            },
            success: function (e) {
                $(e).closest('.form-group').removeClass('has-error').addClass('has-info');
                $(e).remove();
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element.parent());
            },
            submitHandler: function (form) {
                form.submit();
            }
        });
    }
});
