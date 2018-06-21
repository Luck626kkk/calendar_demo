<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Calendar_Demo</title>
         <link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
         <script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
         <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
         <script src="handlebars-v4.0.11.js"></script>
         <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
         <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
       
        <style>
            #calendar{
                margin:0 auto;
                width: 90%;
            }
            #header{
                font-size: 2.4rem;
                font-weight: bold;
            }
            .day,.date-block{
                float:left;
                width:calc(100%/7);
            }
            #dates{
                border-right:1px solid #ccc;
                border-bottom: 1px solid #ccc;
            }
            .date-block{
                border-top: 1px solid #ccc;
                border-left: 1px solid #ccc;
                height: 15vh;
                padding: 4px;
                overflow: auto;
            }
            .date-block.empty{
                background-color: #eee;
            }
            .event{
                margin-bottom: 2px;
                border-radius: 12px;
                padding: 0 6px;
                background: orange;
                color:white;
            }
            .event .title{
                float: left;
            }
            .event .from {
                float: right;
            }

            /* info panel */
            #info-panel{
                display: none;
                position: fixed;
                background-color: white;
                border:1px solid #ccc;
                cursor: progress;
                width: 280px;
            }
            #info-panel.open{
                display: block;
            }
            #info-panel .title, #info-panel .time-picker{
                border-bottom: 1px solid #ccc;
            }
            #info-panel .title, #info-panel .time-picker, #info-panel .description{
                padding: 10px;
            }
            .error-msg{
                display: none;
            }
            .error-msg.open{
                display: block;
            }
            .selected-date{
                text-align: center;
                font-size: 1.6rem;
                font-weight: bold;
            }
            #description{
                width:100%;
                
            }
            #info-panel label{
                color: #aaa;
                font-size: 0.8rem;
            }
            #info-panel .close{
                right:10px;
                top:10px;
                position: relative;
            }
            #info-panel button{
                display: none;
                border: none;
                padding: 10px;
                color: white;
                cursor: progress;
            }
            #info-panel.new button.create, #info-panel.new button.cancel {
                display: block;
                width:50%;
                float: left;
            }
            #info-panel.update button.update, #info-panel.update button.cancel,#info-panel.update button.delete {
                display: block;
                width:50%;
                float:left;
            }
            #info-panel.update button.delete{
                width:100%;
                background: #c21717;
            }
            #info-panel.update button.update, #info-panel.new button.create{
               background: #74be00;
            }

        </style>
    </head>
    <?php include "data.php";?>
    <body>
        <div id="calendar" data-year="<?=date('Y')?>" data-month="<?= date('m')?>">
            <div id="header">
                <?=date('Y')?>/<?= date('m')?>
            </div>
            <div id="days" class="clearfix">
                <div class="day">SUN</div>
                <div class="day">MON</div>
                <div class="day">TUE</div>
                <div class="day">WED</div>
                <div class="day">THU</div>
                <div class="day">FRI</div>
                <div class="day">SAT</div>
            </div>
            <div id="dates" class="clearfix">
                <?php foreach ($dates as $key => $date):?>
                    <div class="date-block <?=(is_null($date)? 'empty' : '') ?>" data-date="<?=$date ?>">
                        <div class="date"><?=$date ?></div>
                        <div class="events">
                        </div>
                    </div>   
                <?php endforeach ?>
            </div>
        </div>

        <div id="info-panel" class="update">
            <div class="close">X</div>
            <form>
                <input type="hidden" name="id">
                <div class="title">
                    <label>event</label><br>
                    <input type="text" name="title">
                    <!--<div contenteditable="true"></div> 上一行取代-->
                </div>
                <div class="error-msg">
                    <div class="alert alert-danger">error</div>
                </div>
                <div class="time-picker clearfix">
                    <div class="selected-date">
                        <span class="month">10</span>/<span class="date">20</span>
                        <input type="hidden" name="year">
                        <input type="hidden" name="month">
                        <input type="hidden" name="date">
                    </div>
                <div class="from">
                    <label for="from">from</label><br>
                    <input id="from" type="time" name="start_time">
                </div>
                <div class="to">
                    <label for="to">to</label><br>
                    <input id="to" type="time" name="end_time">
                </div>
            
            </div>

            <div class="description">
                <label>description</label><br>
                <textarea name="description" id="description"></textarea>
               
            </div>

            </form>

            
            <div class="buttons clearfix">
                <button class="create">create</button>
                <button class="update">update</button>
                <button class="cancel">cancel</button>
                <button class="delete">delete</button>
            </div>
        </div>

    </body>
     <script>
            $(document).ready(function () {

                var source = $('#event-template').html();
                var eventTemplate = Handlebars.compile(source);
                console.log(events);
                $.each(events, function(index,event){
                    var eventUI = eventTemplate(event);
                    var date = event['date'];
                    $('#calendar').find('.date-block[data-date="'+date+'"]').find('.events').append(eventUI);
                });

                var panel ={
                    el: '#info-panel',
                    selectedDateBlock: null ,
                    selectedEvent: null,
                    init: function(e){
                        panel.clear();
                        panel.updateData(e); //抓取 date-block 的日期
                    },
                    open: function(isNew,e){
                        panel.init(e);
                        panel.hideError();
                        var panelWidth= $(panel.el).width();
                        var X = e.pageX; 
                        var windowWidth = $(window).width();
                        //如果超過版面就固定
                        if(X + panelWidth > windowWidth){
                            X = windowWidth -panelWidth;
                        }

                        $(panel.el).addClass('open').css({
                            top: e.pageY+'px',
                            left: X+'px'
                        }).find('[name="title"]').focus();//找到title 底下的 contenteditable


                        if (isNew){
                            $(panel.el).addClass('new').removeClass('update');
                            panel.selectedDateBlock = $(e.currentTarget);
                        }else{
                            $(panel.el).addClass('update').removeClass('new');
                            panel.selectedDateBlock = $(e.currentTarget).closest('.date-block');
                        }
                    },
                    clear: function(){
                        //clear form data
                        $(panel.el).find('input').val("");
                        $(panel.el).find('textarea').val("");
                        //$('input').val("");
                    },
                    close: function(){
                        $(panel.el).removeClass('open');
                    },
                    updateData: function(e){
                        if($(e.currentTarget).is('.date-block')){
                            var date =$(e.currentTarget).data('date');
                        }else{
                            var date =$(e.currentTarget).closest('.date-block').data('date');
                        }

                        var year = $('#calendar').data('year');
                        var month = $('#calendar').data('month');

                        $(panel.el).find('.month').text(month);
                        $(panel.el).find('.date').text(date);
                        //將值塞入 input
                        $(panel.el).find('[name="year"]').val(year);
                        $(panel.el).find('[name="month"]').val(month);
                        $(panel.el).find('[name="date"]').val(date);
                    },
                    showError: function(msg){
                        $(panel.el).find('.error-msg').addClass('open')
                            .find('.alert').text(msg);
                    },
                    hideError: function(){
                        $(panel.el).find('.error-msg').removeClass('open');
                    }
                };

                $('.date-block')
                    .dblclick(function(e){
                    panel.open(true,e);                      
                }).on('dblclick','.event',function(e){
                    e.stopPropagation(); //停止往上傳遞dblclick
                    panel.open(false,e);
                    
                    panel.selectedEvent = $(e.currentTarget);
                    var id = $(this).data('id');
                    //AJAX call -get event detail
                    $.post('read.php',{id: id},function(data,textStatus,xhr){
                        //console.log(data);
                        $(panel.el).find('[name="id"]').val(data.id);
                        $(panel.el).find('[name="title"]').val(data.title);
                        $(panel.el).find('[name="start_time"]').val(data.start_time);
                        $(panel.el).find('[name="end_time"]').val(data.end_time);
                        $(panel.el).find('[name="description"]').val(data.description);

                    }).fail(function(xhr){
                        panel.showError(xhr.responseText);
                    });
                    //load detail back to panel
                });


                $('#info-panel')
                    .on('click','button',function(e){
                        if ($(this).is('.create') || $(this).is('.update')){
                            if($(this).is('.create'))
                                var action = 'create.php';
                            if($(this).is('.update'))
                                var action = 'update.php';

                            //collect data
                            var data = $(panel.el).find('form').serialize();
                            console.log(data);
                            //AJAX call - create API
                            
                            $.post(action,data,function(){})
                                .done(function(data,textStatus,xhr){
                                    //如果是 update 必須要把舊的橘色欄刪除
                                    if($(e.currentTarget).is('.update'))
                                        panel.selectedEvent.remove();


                                    //insert into event
                                    console.log(data);
                                    var eventUI = eventTemplate(data);

                                    //比較時間點 排出順序
                                    //先找到點擊區塊的 event
                                    panel.selectedDateBlock.find('.event').each(function(index, event){
                                        var eventFromTime = $(event).data('from').split(':'); //第一個 $ 是將 HTML 轉成 jquery
                                        var newEventFromTime = data.start_time.split(':');
                                        if (eventFromTime[0]>newEventFromTime[0] ||
                                         (eventFromTime[0] == newEventFromTime[0] && eventFromTime[1]>newEventFromTime[1])){

                                            $(event).before(eventUI);
                                            return false; //中斷迴圈 當達到目的就讓它中斷 break 的意思
                                        }
                                    });
                                    if(panel.selectedDateBlock.find('.event[data-id="'+data.id+'"]').length == 0){
                                        panel.selectedDateBlock.find('.events').append(eventUI);
                                    }
                                    panel.close();
                                })
                                .fail(function(xhr,textStatus,errorThrown){
                                    panel.showError(xhr.responseText);
                                    console.log(xhr.responseText);

                            });  
                        }

                        if ($(this).is('.update')){
                            //collect form data
                            //var event = $(panel.el).find('form').serialize();
                            //console.log(event);
                            //AJAX call - update.php with id
                            //update event UI

                        }
                        if ($(this).is('.cancel')){
                            panel.close(); //取代下面那行
                            //$('#info-panel').removeClass('open');
                        }
                        if ($(this).is('.delete')){
                            var result = confirm('Do you really want to delete this event ?')

                            if (result){
                                // id
                                var id = panel.selectedEvent.data('id');
                                // AJAX call - delete.php with id
                                $.post('delete.php',{id: id})
                                    .done(function(){
                                        //remove event from calendar
                                        panel.selectedEvent.remove();
                                        panel.close();
                                    });
                            }
                            
                        }
                    })
                    .on('click','.close',function(e){
                        //$('#info-panel').removeClass('open');
                        $('button.cancel').click(); //找到 cancel 這個 button 讓它自己去點擊 cancel 這個按紐
                    });


            });
    </script>
    <script id="event-template" type="text/x-handlebars-template">
        <div class="event clearfix" data-id="{{id}}" data-from="{{start_time}}">
            <div class="title">{{title}}</div>
            <div class="from">{{start_time}}</div>
        </div>
       
    </script>
</html>
