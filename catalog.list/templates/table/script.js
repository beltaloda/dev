function city_add(elem){
        var data = '',
            _this=$(elem);
        
        btn=$(elem).find('.btn-primary');
        btn_txt=btn.val();          
        btn.val('Loading...');
        
        data=$(elem).serialize();
        url=$(elem).attr("action");
        
        $.ajax({
            type: "POST",
            data: data,
            dataType:'json',
            url: url,
            success: function(data)
            {   
                _this.find('.alert').remove();
                if(data.type=='success'){
                    $('#city_table').prepend(data.html);
                    _this.append('<div class="alert alert-success" role="alert"><strong>'+data.msg+'</div>');
                   /* if($(data).find('#catalogList')){
                        html=$(data).find('#catalogList').html()
                        $('#catalogList').html(html);
                        _this.append('<div class="alert alert-success" role="alert"><strong>Город добавлен</div>');
                         
                    }else{
                        _this.append('<div class="alert alert-danger" role="alert"><strong>'+data+'</div>');
                    }*/               
                }else{
                    _this.append('<div class="alert alert-danger" role="alert"><strong>'+data.msg+'</div>');
                }  
                _this[0].reset();
    
            },
            fail: function(){
                alert('ajax fail');
            },
            error: function(data)
            {
                alert('ajax error');
            } 
        });  
        btn.val(btn_txt);      
}


function city_del(elem){
        var data = '',
            id=$(elem).data('city_id'),
            _this=$(elem);
        
        data={'ajax':'Y','city_id':id,'action':'delete_city'};
        url=$(elem).attr("action");

        btn_txt=$(elem).html();          
        $(elem).html('Loading...');

        $.ajax({
            type: "POST",
            data: data,
            dataType:'json',
            url: url,
            success: function(data)
            {   
                $('#add-form').find('.alert').remove();
                if(data.type=='success'){  
                    _this.parents('#city_'+id).remove();
                 }else{
                    $('#add-form').append('<div class="alert alert-danger" role="alert"><strong>'+data.msg+'</div>');
                 }
 
    
            },
            fail: function(){
                alert('ajax fail');
                $(elem).html(btn_txt);
            },
            error: function(data)
            {
                alert('ajax error');
                $(elem).html(btn_txt);
            } 
        });       
}
