<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <title>Письма</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous"/>
        <style type="text/css">
            .panel .panel-heading * {display: inline-block;}
            .panel .panel-body {padding: 0;}
            .list-group {margin: 0}
            .list-group .list-group-item {border-left: 0;border-right: 0;}
            .list-group .list-group-item:nth-child(even) {background: #f9f9f9;}
            .list-group .list-group-item:first-child {border-top: 0;}
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <a href="/refresh.php">
                            <i class="glyphicon glyphicon-refresh"></i>
                        </a>
                        <h3 class="panel-title">Письма</h3>
                    </div>
                    <div class="panel-body">
                        <ul class="list-group">
                            <?php foreach($mails as $mail) { ?>
                                <li class="list-group-item">
                                    <a href="<?=$mail['link']?>">
                                        <?=$mail['to']?> - <?=$mail['subject']?>, <?=$mail['date']?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>