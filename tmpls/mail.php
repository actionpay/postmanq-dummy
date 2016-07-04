<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <title><?=$subject?></title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous"/>
        <style type="text/css">
            .panel .panel-heading * {display: inline-block;}
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a href="/index.html">
                            <i class="glyphicon glyphicon-chevron-left" aria-hidden="true"></i>
                        </a>
                        <h3 class="panel-title"><?=$subject?></h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Тема:</label>
                                <div class="col-sm-10">
                                    <p id="subject" class="form-control-static"><?=$subject?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">От:</label>
                                <div class="col-sm-10">
                                    <p class="form-control-static"><?=$from?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Кому:</label>
                                <div class="col-sm-10">
                                    <p id="to" class="form-control-static"><?=$to?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Дата:</label>
                                <div class="col-sm-10">
                                    <p id="date" class="form-control-static"><?=$date?></p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Сообщение:</label>
                                <div class="col-sm-10 form-control-static">
                                    <div class="embed-responsive embed-responsive-4by3">
                                        <iframe class="embed-responsive-item" src="<?=$body?>"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>