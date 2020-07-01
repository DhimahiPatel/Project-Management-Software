<?php
if (!defined('APPLICATION_LOADED') || !APPLICATION_LOADED) {
    die('No direct script access.');
}
if (!in_array($GLOBALS['CONFIG']['PERMISSIONS']['SETTINGS_PAGE'], $this->permissions)) {
    die($this->lang_php['not_permissions']);
}
require 'templates/list_users.php';

$this->title = $_SERVER['HTTP_HOST'] . ' - ' . $this->lang_php['title_settings'];

$projects_list = $this->getProjects();
?>
<script src="<?= base_url('assets/js/ckeditor/ckeditor.js') ?>"></script>
<div id="settings">
    <nav class="navbar navbar-settings navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="#"><?= $this->lang_php['general_settings'] ?></a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right">
                    
                    <li><a href="<?= base_url('profile/' . $this->user_name) ?>"><?= $this->lang_php['profile'] ?></a></li>
                    <li><a href="<?= base_url('home') ?>"><?= $this->lang_php['home'] ?></a></li>
                   
                </ul>
                <form class="navbar-form navbar-right form-s">
                    <input type="text" class="form-control" name="settings_search" placeholder="<?= $this->lang_php['search'] ?>">
                    <div class="list-group" id="settings-query-result">

                    </div>
                    <img src="<?= base_url('assets/imgs/settings-search-spinner.gif') ?>" alt="loading" class="s-loading">
                </form>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-3 col-md-2 sidebar">
                <ul class="nav nav-sidebar">
                    <li class="<?= url_segment(1) === 'profiles' ? 'active' : '' ?>"><a href="<?= base_url('settings/profiles') ?>"><?= $this->lang_php['profiles'] ?><span class="sr-only">(current)</span></a></li>
                    <li class="<?= url_segment(1) == 'projects' ? 'active' : '' ?>"><a href="<?= base_url('settings/projects') ?>"><?= $this->lang_php['projects'] ?></a></li>
                </ul>
            </div>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <?php if (url_segment(1) === false) { ?>
                    <?php
                    $find = null;
                    if (isset($_GET['gofilter'])) {
                        $find = $_GET;
                    }
                    $res_tickets = $this->getTicketStatistics($find);

                    if (!empty($res_tickets)) {
                        $months_categories = "'" . implode("','", array_keys($res_tickets['months'])) . "'";

                        if ($res_tickets['num_created'] != null) {
                            $months_created = "";
                            foreach ($res_tickets['num_created'] as $p => $m) {
                                $color[] = $m['color'];
                                $months_created .= "{";
                                $months_created .= "name: '$p',";
                                $months_created .= "data: [" . implode(', ', $m['nums']) . "]";
                                $months_created .= "},";
                            }
                            $colors = "'" . implode("', '", $color) . "'";
                            $months_created = rtrim($months_created, ",");
                        }

                        if ($res_tickets['num_closed'] != null) {
                            $months_closed = "";
                            foreach ($res_tickets['num_closed'] as $p => $m) {
                                $color_c[] = $m['color'];
                                $months_closed .= "{";
                                $months_closed .= "name: '$p',";
                                $months_closed .= "data: [" . implode(', ', $m['nums']) . "]";
                                $months_closed .= "},";
                            }
                            $colors_c = "'" . implode("', '", $color_c) . "'";
                            $months_closed = rtrim($months_closed, ",");
                        }

                        $percents_tickets = '';
                        foreach ($res_tickets['num_for_priority'] as $pr => $num_p) {
                            $division = $num_p / $res_tickets['num_all'];
                            $percent = $division * 100;

                            $percents_tickets .= "{";
                            $percents_tickets .= "name: '$pr',";
                            $percents_tickets .= "y: " . number_format($percent);
                            $percents_tickets .= "},";
                        }
                        $percents_tickets = rtrim($percents_tickets, ",");
                    }
                    $theLog = $this->getSettingsActivityLog();
                    require 'templates/activitystream.php';
                    ?>
                    <div id="settings-overview">
                        <h1><?= $this->lang_php['overview'] ?>:</h1>
                        <hr>
                        <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-datepicker.min.css') ?>">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#settings-stat-tab" aria-controls="settings-stat-tab" role="tab" data-toggle="tab"><?= $this->lang_php['statistics'] ?></a></li>
                            <li role="presentation"><a href="#settings-activ-tab" aria-controls="settings-activ-tab" role="tab" data-toggle="tab"><?= $this->lang_php['activity_log'] ?></a></li>
                        </ul>

                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in active" id="settings-stat-tab">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <form method="GET" action="">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <select name="project" class="selectpicker form-control show-menu-arrow">
                                                        <option value=""><?= $this->lang_php['all_projects'] ?></option>
                                                        <?php foreach ($projects_list as $proj) { ?>
                                                            <option <?= isset($_GET['project']) && $_GET['project'] == $proj['id'] ? 'selected' : '' ?> value="<?= $proj['id'] ?>"><?= $proj['name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="input-group">
                                                        <span id="from" class="input-group-addon"><?= $this->lang_php['from'] ?>:</span>
                                                        <input class="form-control date-pick" type="text" aria-describedby="from" placeholder="<?= $this->lang_php['date'] ?>" value="<?= isset($_GET['from_date']) ? $_GET['from_date'] : '' ?>" name="from_date">
                                                        <span id="to" class="input-group-addon"><?= $this->lang_php['to'] ?>:</span>
                                                        <input class="form-control date-pick" type="text" aria-describedby="to" placeholder="<?= $this->lang_php['date'] ?>" value="<?= isset($_GET['to_date']) ? $_GET['to_date'] : '' ?>" name="to_date">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <input type="submit" class="btn btn-primary" value="<?= $this->lang_php['filter_it'] ?>" name="gofilter">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <h1><?= $this->lang_php['statistics'] ?></h1>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <script>
                                            $(function () {
                                                

                                                // Load the fonts
                                                Highcharts.createElement('link', {
                                                    href: '//fonts.googleapis.com/css?family=Signika:400,700',
                                                    rel: 'stylesheet',
                                                    type: 'text/css'
                                                }, null, document.getElementsByTagName('head')[0]);

                                                // Add the background image to the container
                                                Highcharts.wrap(Highcharts.Chart.prototype, 'getContainer', function (proceed) {
                                                    proceed.call(this);
                                                    this.container.style.background = 'url(http://www.highcharts.com/samples/graphics/sand.png)';
                                                });


                                                Highcharts.theme = {
                                                    colors: ["#f45b5b", "#8085e9", "#8d4654", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
                                                        "#55BF3B", "#DF5353", "#7798BF", "#aaeeee"],
                                                    chart: {
                                                        backgroundColor: null,
                                                        style: {
                                                            fontFamily: "Signika, serif"
                                                        }
                                                    },
                                                    title: {
                                                        style: {
                                                            color: 'black',
                                                            fontSize: '16px',
                                                            fontWeight: 'bold'
                                                        }
                                                    },
                                                    subtitle: {
                                                        style: {
                                                            color: 'black'
                                                        }
                                                    },
                                                    tooltip: {
                                                        borderWidth: 0
                                                    },
                                                    legend: {
                                                        itemStyle: {
                                                            fontWeight: 'bold',
                                                            fontSize: '13px'
                                                        }
                                                    },
                                                    xAxis: {
                                                        labels: {
                                                            style: {
                                                                color: '#6e6e70'
                                                            }
                                                        }
                                                    },
                                                    yAxis: {
                                                        labels: {
                                                            style: {
                                                                color: '#6e6e70'
                                                            }
                                                        }
                                                    },
                                                    plotOptions: {
                                                        series: {
                                                            shadow: true
                                                        },
                                                        candlestick: {
                                                            lineColor: '#404048'
                                                        },
                                                        map: {
                                                            shadow: false
                                                        }
                                                    },
                                                    // Highstock specific
                                                    navigator: {
                                                        xAxis: {
                                                            gridLineColor: '#D0D0D8'
                                                        }
                                                    },
                                                    rangeSelector: {
                                                        buttonTheme: {
                                                            fill: 'white',
                                                            stroke: '#C0C0C8',
                                                            'stroke-width': 1,
                                                            states: {
                                                                select: {
                                                                    fill: '#D0D0D8'
                                                                }
                                                            }
                                                        }
                                                    },
                                                    scrollbar: {
                                                        trackBorderColor: '#C0C0C8'
                                                    },
                                                    // General
                                                    background2: '#E0E0E8'

                                                };

                                                // Apply the theme
                                                Highcharts.setOptions(Highcharts.theme);
                                            });
                                        </script>
                                        <?php
                                        if (!empty($res_tickets) && $res_tickets['num_created'] != null) {
                                            ?>
                                            <script>
                                                $(function () {
                                                    $('#monthly-created-tickets').highcharts({
                                                        title: {
                                                            text: lang.highcharts_monthly_created,
                                                            x: -20
                                                        },
                                                        colors: [<?= $colors ?>],
                                                        xAxis: {
                                                            categories: [<?= $months_categories ?>]
                                                        },
                                                        yAxis: {
                                                            title: {
                                                                text: 'Number'
                                                            },
                                                            plotLines: [{
                                                                    value: 0,
                                                                    width: 1,
                                                                    color: '#808080'
                                                                }]
                                                        },
                                                        tooltip: {
                                                            valueSuffix: ''
                                                        },
                                                        legend: {
                                                            layout: 'vertical',
                                                            align: 'right',
                                                            verticalAlign: 'middle',
                                                            borderWidth: 0
                                                        },
                                                        series: [<?= $months_created ?>]
                                                    });
                                                });
                                            </script>
                                            <div id="monthly-created-tickets"></div>
                                            <hr>
                                        <?php } else { ?>
                                            <div class="alert alert-info">NO statistic created for projects</div>
                                            <?php
                                        }
                                        if (!empty($res_tickets) && $res_tickets['num_closed'] != null) {
                                            ?>
                                            <script>$(function () {
                                                    $('#monthly-closed-tickets').highcharts({
                                                        title: {
                                                            text: lang.highcharts_monthly_closed,
                                                            x: -20
                                                        },
                                                        colors: [<?= $colors_c ?>],
                                                        xAxis: {
                                                            categories: [<?= $months_categories ?>]
                                                        },
                                                        yAxis: {
                                                            title: {
                                                                text: 'Number'
                                                            },
                                                            plotLines: [{
                                                                    value: 0,
                                                                    width: 1,
                                                                    color: '#808080'
                                                                }]
                                                        },
                                                        tooltip: {
                                                            valueSuffix: ''
                                                        },
                                                        legend: {
                                                            layout: 'vertical',
                                                            align: 'right',
                                                            verticalAlign: 'middle',
                                                            borderWidth: 0
                                                        },
                                                        series: [<?= $months_closed ?>]
                                                    });
                                                });
                                            </script>
                                            <div id="monthly-closed-tickets"></div>
                                            <hr>
                                        <?php } else { ?>
                                            <div class="alert alert-info"><?= $this->lang_php['no_stat_for_tickets_closed'] ?></div>
                                            <?php
                                        }

                                        if (isset($percents_tickets) && $percents_tickets != null) {
                                            ?>
                                            <script>
                                                $(function () {
                                                    $('#tickets-with-priorities').highcharts({
                                                        chart: {
                                                            plotBackgroundColor: null,
                                                            plotBorderWidth: null,
                                                            plotShadow: false,
                                                            type: 'pie'
                                                        },
                                                        title: {
                                                            text: lang.highcharts_by_priority
                                                        },
                                                        colors: [<?= $colors ?>],
                                                        tooltip: {
                                                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                                                        },
                                                        plotOptions: {
                                                            pie: {
                                                                allowPointSelect: true,
                                                                cursor: 'pointer',
                                                                dataLabels: {
                                                                    enabled: false
                                                                },
                                                                showInLegend: true
                                                            }
                                                        },
                                                        series: [{
                                                                name: lang.highcharts_priority,
                                                                colorByPoint: true,
                                                                data: [<?= $percents_tickets ?>]
                                                            }]
                                                    });
                                                });
                                            </script>
                                            <div id="tickets-with-priorities"></div>
                                        <?php } ?>
                                    </div>
                                </div>
                               
                                <div class="row">
                                    <div class="col-xs-12">
                                        <?php
                                        if (!empty($res_wiki)) {
                                            ?>
                                            <script>
                                                $(function () {
                                                    $('#monthly-created-pages').highcharts({
                                                        title: {
                                                            text: lang.highcharts_monthly_created_pages,
                                                            x: -20
                                                        },
                                                        xAxis: {
                                                            categories: [<?= $months_categories_w ?>]
                                                        },
                                                        yAxis: {
                                                            title: {
                                                                text: 'Number'
                                                            },
                                                            plotLines: [{
                                                                    value: 0,
                                                                    width: 1,
                                                                    color: '#808080'
                                                                }]
                                                        },
                                                        tooltip: {
                                                            valueSuffix: ' pages'
                                                        },
                                                        legend: {
                                                            layout: 'vertical',
                                                            align: 'right',
                                                            verticalAlign: 'middle',
                                                            borderWidth: 0
                                                        },
                                                        series: [<?= $months_created_w ?>]
                                                    });
                                                });
                                            </script>
                                            <div id="monthly-created-pages"></div>
                                        <?php } else { ?>
                                            <div class="alert alert-info"><?= $this->lang_php['no_stat_for_pages'] ?></div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div role="tabpanel" class="tab-pane fade" id="settings-activ-tab">
                                <h1><?= $this->lang_php['activity_log'] ?></h1>
                                <div class="panel panel-info panel-activity">
                                    <div class="panel-heading"><?= $this->lang_php['activity'] ?></div>
                                    <div class="panel-body">
                                        <?php
                                        if (!empty($theLog)) {
                                            activityForeach($theLog, 2);
                                            ?>
                                            <div id="w-ajax"></div>
                                            <a href="javascript:void(0)" onClick="showMore(<?= $this->project_id ?>)" class="bordered text-center show-more"><?= $this->lang_php['show_more'] ?> <i class="fa fa-chevron-circle-down"></i></a>
                                            <?php
                                        } else {
                                            ?>
                                            <?= $this->lang_php['no_activity'] ?>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script src="<?= base_url('assets/js/bootstrap-datepicker.min.js') ?>"></script>
                    <script src="<?= base_url('assets/js/highcharts/highcharts.js') ?>"></script>
                    <script src="<?= base_url('assets/js/highcharts/modules/exporting.js') ?>"></script>
                    <script>
                                                if (window.location.hash == '#settings-activ-tab') {
                                                    alert();
                                                }

                                                function goAjax(projectid, from, to) {
                                                    $.ajax({
                                                        type: "POST",
                                                        url: "activity_settings",
                                                        data: {from: from, to: to}
                                                    }).done(function (data) {
                                                        $("#w-ajax").append(data);
                                                    });
                                                }
                                                to = 10;
                                                from = 0;
                                                function showMore(projectid) {
                                                    to += 10;
                                                    from += 10;
                                                    goAjax(projectid, from, to);
                                                }

                                                $('.date-pick').datepicker({
                                                    calendarWeeks: true,
                                                    autoclose: true,
                                                    todayHighlight: true,
                                                    format: 'dd.mm.yyyy'
                                                });
                    </script>
                    <?php
                }
                 if (url_segment(1) !== false && url_segment(1) == 'general') {
                    $abbreviations = getFolderLanguages();
                    if (isset($_POST['setlanguage'])) {
                        $result = $this->setLanguage($_POST['language']);
                        if ($result === true) {
                            $this->set_alert($this->lang_php['language_changed'] . '!', 'success');
                        } else {
                            $this->set_alert($this->lang_php['language_changed_err'] . '!', 'danger');
                        }
                        redirect('general');
                    }
                    if (isset($_POST['setloginimage'])) {
                        $img_name = $this->uploadImage($GLOBALS['CONFIG']['IMAGELOGINUPLOADDIR']);
                        $result = $this->setLoginImage($img_name);
                        if ($result === true) {
                            $this->set_alert($this->lang_php['login_image_upload'] . '!', 'success');
                        } else {
                            $this->set_alert($this->lang_php['login_image_upload_err'] . '!', 'danger');
                        }
                        redirect('general');
                    }
                    $login_image = $this->getLoginImage();
                    if (isset($_GET['removeLoginImage'])) {
                        $this->removeLoginImage($GLOBALS['CONFIG']['IMAGELOGINUPLOADDIR'] . $login_image);
                        redirect('general');
                    }
                    if (isset($_POST['setDefCurrency'])) {
                        $result = $this->setDefCurrency($_POST['def_currency']);
                        if ($result === true) {
                            $this->set_alert($this->lang_php['currency_changed'] . '!', 'success');
                        } else {
                            $this->set_alert($this->lang_php['currency_change_err'] . '!', 'danger');
                        }
                        redirect('general');
                    }
                    $currencies = $this->getCurrencies();
                    echo $this->get_alert();
                    ?>
                    <h1><?= $this->lang_php['general'] ?>:</h1>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><?= $this->lang_php['site_default_language'] ?></div>
                                <div class="panel-body">
                                    <form method="post" action="">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                                                <select name="language" class="selectpicker form-control show-tick show-menu-arrow">
                                                    <?php foreach ($abbreviations as $abbreviature) { ?>
                                                        <option <?= $abbreviature == $this->default_lang_abbr ? 'selected' : '' ?> value="<?= $abbreviature ?>"><?= $abbreviature ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
                                                <input type="submit" name="setlanguage" class="btn btn-info" value="<?= $this->lang_php['save'] ?>">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><?= $this->lang_php['login_image'] ?></div>
                                <div class="panel-body">
                                    <form method="post" enctype="multipart/form-data" action="">
                                        <div class="col-xs-8">
                                            <div class="form-group">
                                                <img class="profile-img" src="<?= $login_image != null ? base_url($GLOBALS['CONFIG']['IMAGELOGINUPLOADDIR'] . $login_image) : '' ?>" alt="no image" style="width:90px;">
                                                <label class="col-sm-2 control-label"><?= $this->lang_php['image'] ?></label>
                                                <div class="col-sm-10">
                                                    <input type="file" name="image" id="fileToUpload">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-4">
                                            <input type="submit" name="setloginimage" class="btn btn-info" value="<?= $this->lang_php['save'] ?>">
                                            <?php if ($login_image != null) { ?>
                                                <a href="?removeLoginImage=true" class="btn btn-danger"><?= $this->lang_php['remove'] ?></a>
                                            <?php } ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="panel panel-default">
                                <div class="panel-heading"><?= $this->lang_php['price_per_hour'] . '/' . $this->lang_php['default_currency'] ?></div>
                                <div class="panel-body">
                                    <form method="post" action="">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
                                                <select name="def_currency" class="selectpicker form-control show-tick show-menu-arrow">
                                                    <?php foreach ($currencies as $currencie) { ?>
                                                        <option <?= $currencie['def'] == 1 ? 'selected' : '' ?> value="<?= $currencie['currency'] ?>"><?= $currencie['country'] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2">
                                                <input type="submit" name="setDefCurrency" class="btn btn-info" value="<?= $this->lang_php['save'] ?>">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                if (url_segment(1) !== false && url_segment(1) == 'profiles') {
                    echo $this->get_alert();
                    ?>
                    <h1><?= $this->lang_php['profiles'] ?>:</h1>
                    <hr>
                    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#addUser">
                        <?= $this->lang_php['add_user'] ?>
                    </button>
                    <div class="profiles-boxes">
                        <hr>
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="panel panel-default">
                                    <div class="panel-body p-t-0">
                                        <form method="GET" action="">
                                            <div class="input-group">
                                                <input type="text" id="example-input1-group2" value="<?= isset($_GET['find-user']) ? urldecode($_GET['find-user']) : '' ?>" name="find-user" class="form-control" placeholder="Search">
                                                <span class="input-group-btn">
                                                    <button type="submit" class="btn btn-effect-ripple btn-primary"><i class="fa fa-search"></i></button>
                                                </span>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php
                            $abbreviations = getFolderLanguages();
                            if (isset($_POST['update'])) {
                                $img_name = $this->uploadImage($GLOBALS['CONFIG']['IMAGESUSERSUPLOADDIR']);
                                if ($img_name !== null) {
                                    $_POST['image'] = $img_name;
                                }
                                $result = $this->setUser($_POST);
                                if ($result == false) {
                                    $this->set_alert($this->lang_php['db_err'], 'danger');
                                } else {
                                    $this->set_alert($this->lang_php['user_updated'], 'success');
                                }
                                redirect('profiles' . getQueryStr($_GET, 'page'));
                            }

                            if (isset($_GET['delete_user'])) {
                                $this->deleteUser($_GET['delete_user']);
                                redirect('profiles' . getQueryStr($_GET, array('page', 'delete_user')));
                            }

                            $search = isset($_GET['find-user']) ? $_GET['find-user'] : null;
                            $num_results = $this->getCountProfiles($search);

                            $num_pages = ceil($num_results / RESULT_LIMIT_SETTINGS_PROFILES);
                            $pg = isset($_GET['pg']) ? intval($_GET['pg']) : 0;
                            $pg = min($pg, $num_pages);
                            $pg = max($pg, 0);
                            $from = RESULT_LIMIT_SETTINGS_PROFILES * ($pg);

                            $profiles = $this->getUsers($from, RESULT_LIMIT_SETTINGS_PROFILES, $search);
                            $professions = $this->getProfessions();
                            $arr_lang = array(
                                'registered' => $this->lang_php['registered'],
                                'last_login' => $this->lang_php['last_login'],
                                'last_active' => $this->lang_php['last_active'],
                                'preview' => $this->lang_php['preview']
                            );
                            if (!empty($profiles)) {
                                loop_users($profiles, true, $arr_lang);
                                if (isset($_GET['find-user']) && $_GET['find-user'] != '') {
                                    $query_string = '&find-user=' . $_GET['find-user'];
                                } else {
                                    $query_string = '';
                                }
                            } else {
                                ?>
                                <h3><?= $this->lang_php['no_users_found'] ?> <?= isset($_GET['find-user']) && $_GET['find-user'] != '' ? $this->lang_php['with_name'] . ' <i>' . $_GET['find-user'] . '</i>!' : '!' ?></h3>
                            <?php } ?>
                        </div>
                        <?= paging('settings/profiles', $query_string, $num_results, $pg, RESULT_LIMIT_SETTINGS_PROFILES) ?>
                    </div>
                    <!-- Add User Modal -->
                    <div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel"><?= $this->lang_php['add_profile'] ?></h4>
                                </div>
                                <div class="modal-body">
                                    <form class="form-horizontal" id="user-add" role="form" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="update" value="0">
                                        <div class="alert alert-danger" id="reg-errors" style="display:none;"></div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?= $this->lang_php['username'] ?> *</label>
                                            <div class="col-sm-10">
                                                <input class="form-control" name="username" type="text" value="">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?= $this->lang_php['pass'] ?> *</label>
                                            <div class="col-sm-10">
                                                <input class="form-control" id="pwd" name="password" type="text" value="">
                                                <span><?= $this->lang_php['pass_strenght'] ?>:</span>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                                                    </div>
                                                </div>
                                                <button type="button" id="GeneratePwd" class="btn btn-default"><?= $this->lang_php['gen_pass'] ?></button> 
                                                <p id="demo"></p>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?= $this->lang_php['full_name'] ?> *</label>
                                            <div class="col-sm-10">
                                                <input class="form-control" name="fullname" type="text" value="">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <img src="" alt="none" style="display:none;" id="cover">
                                            <label class="col-sm-2 control-label"><?= $this->lang_php['image'] ?></label>
                                            <div class="col-sm-10">
                                                <input type="file"  name="image" id="fileToUpload">
                                            </div>
                                        </div>
                                       
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Email * <i class="fa fa-envelope"></i></label>
                                            <div class="col-sm-10">
                                                <input class="form-control" name="email" type="text" value="">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?= $this->lang_php['profession'] ?></label>
                                            <div class="col-sm-10">
                                                <input type="hidden" value="0" name="new-prof">
                                                <input class="form-control" id="add-new-prof-field" name="new-prof-name" style="display:none;" type="text" value="">
                                                <select class="form-control" name="prof" id="prof-list">
                                                    <option value="0" selected="selected"></option>
                                                    <?php foreach ($professions as $prof) { ?>
                                                        <option value="<?= $prof['id'] ?>"><?= $prof['name'] ?></option>
                                                    <?php } ?>
                                                </select>
                                                <a href="javascript:void(0)" id="add-new-prof" class="btn btn-default"><?= $this->lang_php['add_new'] ?></a>
                                                <a href="javascript:void(0)" id="close-prof-list" class="btn btn-default" style="display:none;"><?= $this->lang_php['show_professions_list'] ?></a>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group"></div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?= $this->lang_php['can_see_projects'] ?></label>
                                            <div class="col-sm-10">
                                                <label class="checkbox-inline"><input type="checkbox" id="select-all-projs" /><?= $this->lang_php['select_all'] ?></label>
                                                <?php foreach ($projects_list as $proj) { ?>
                                                    <label class="checkbox-inline"><input type="checkbox" class="proj-checkbox" name="can_see_proj[]" value="<?= $proj['id'] ?>"><?= $proj['name'] ?></label>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label"><?= $this->lang_php['user_type'] ?></label>
                                            <div class="col-sm-10">
                                                <input type="hidden" value="0" name="costom-privileges">
                                                <select class="form-control" name="default-priv" id="privileges">
                                                    <?php foreach ($GLOBALS['CONFIG']['DEFAULT_USER_TYPES'] as $ukey => $utype) { ?>
                                                        <option <?= $ukey == 'User' ? 'selected' : '' ?> value="<?= $utype ?>"><?= $ukey ?></option>
                                                    <?php } ?>
                                                </select>
                                                <div id="privileges-types" style="display:none;">
                                                    <p><b><?= $this->lang_php['general'] ?></b></p>
                                                    <?php
                                                    foreach ($GLOBALS['CONFIG']['PERMISSIONS'] as $pkey => $pval) {
                                                        if (!is_array($pval)) {
                                                            ?>
                                                            <label class="checkbox-inline"><input type="checkbox" class="usrtype-checkbox" name="custom-priv[]" value="<?= $pval ?>" /><?= $pkey ?></label>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                    <p><b><?= $this->lang_php['tickets'] ?></b></p>
                                                    <?php
                                                    foreach ($GLOBALS['CONFIG']['PERMISSIONS']['TICKETS'] as $pkey => $pval) {
                                                        if (!is_array($pval)) {
                                                            ?>
                                                            <label class="checkbox-inline"><input type="checkbox" class="usrtype-checkbox" name="custom-priv[]" value="<?= $pval ?>" /><?= $pkey ?></label>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                    <p><b><?= $this->lang_php['wiki'] ?></b></p>
                                                    <?php
                                                    foreach ($GLOBALS['CONFIG']['PERMISSIONS']['WIKI'] as $pkey => $pval) {
                                                        if (!is_array($pval)) {
                                                            ?>
                                                            <label class="checkbox-inline"><input type="checkbox" class="usrtype-checkbox" name="custom-priv[]" value="<?= $pval ?>" /><?= $pkey ?></label>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <a href="javascript:void(0)" id="custom-privileges" class="btn btn-default"><?= $this->lang_php['custom_privileges'] ?></a>
                                                <a href="javascript:void(0)" id="custom-privileges-cancel" style="display:none;" class="btn btn-default"><?= $this->lang_php['default_privileges'] ?></a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->lang_php['close'] ?></button>
                                    <button type="button" onclick="validateForm()" class="btn btn-primary"><?= $this->lang_php['save'] ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script type="text/javascript" src="<?= base_url('assets/js/zxcvbn.js') ?>"></script>
                    <script type="text/javascript" src="<?= base_url('assets/js/zxcvbn_bootstrap3.js') ?>"></script>
                    <script type="text/javascript" src="<?= base_url('assets/js/pGenerator.jquery.js') ?>"></script>
                    <script>
                                        $(document).ready(function () {
                                            //PassStrength 
                                            checkPass();
                                            $("#pwd").on('keyup', function () {
                                                checkPass();
                                            });
                                            //PassGenerator
                                            $('#GeneratePwd').pGenerator({
                                                'bind': 'click',
                                                'passwordLength': 9,
                                                'uppercase': true,
                                                'lowercase': true,
                                                'numbers': true,
                                                'specialChars': false,
                                                'onPasswordGenerated': function (generatedPassword) {
                                                    $("#pwd").val(generatedPassword);
                                                    checkPass();
                                                }
                                            });
                                            $("#add-new-prof").click(function () {
                                                $("#prof-list, #add-new-prof").hide();
                                                $("#add-new-prof-field, #close-prof-list").show();
                                                $("[name='new-prof']").val(1);
                                            });
                                            $("#close-prof-list").click(function () {
                                                $("#prof-list, #add-new-prof").show();
                                                $("#add-new-prof-field, #close-prof-list").hide();
                                                $("[name='new-prof']").val(0);
                                            });
                                            $('#select-all-projs').click(function (event) {
                                                if (this.checked) {
                                                    var ch = true;
                                                } else {
                                                    var ch = false;
                                                }
                                                $('.proj-checkbox').each(function () {
                                                    this.checked = ch;
                                                });
                                            });
                                            $("#custom-privileges").click(function () {
                                                $("#custom-privileges-cancel, #privileges-types").show();
                                                $("#privileges, #custom-privileges").hide();
                                                $("[name='costom-privileges']").val(1);
                                            });
                                            $("#custom-privileges-cancel").click(function () {
                                                $("#custom-privileges-cancel, #privileges-types").hide();
                                                $("#privileges, #custom-privileges").show();
                                                $("[name='costom-privileges']").val(0);
                                            });
                                            $('#default_lang').click(function () {
                                                if ($(this).prop("checked") == true) {
                                                    $("[name='lang']").prop("disabled", true);
                                                } else {
                                                    $("[name='lang']").prop("disabled", false);
                                                }
                                            });
                                        });
                                        function takenCheck(name) {
                                            var output;
                                            $.ajax({
                                                type: "POST",
                                                async: false,
                                                url: "<?= base_url('getuserinfo') ?>",
                                                data: {uname: name}
                                            }).done(function (data) {
                                                if (data == 1) {
                                                    output = true;
                                                }
                                            });
                                            return output;
                                        }

                                        function validateForm() {
                                            var usr = $("[name='username']").val();
                                            var pwd = $("[name='password']").val();
                                            var fname = $("[name='fullname']").val();
                                            var email = $('[name="email"]').val();
                                            var reg = <?= $GLOBALS['CONFIG']['EMAILREGEX'] ?>;
                                            var errors = new Array();
                                            if (jQuery.trim(usr).length < 4 || jQuery.trim(usr).length > 20 || jQuery.trim(usr).search(<?= $GLOBALS['CONFIG']['USERNAMEREGEX'] ?>) === -1) {
                                                errors[0] = '<strong>' + lang.username_wrong + '</strong> ' + lang.username_must_be;
                                            } else if (takenCheck(jQuery.trim(usr)) != true && $("[name='update']").val() == 0) {
                                                errors[1] = '<strong>' + lang.username_taken + '</strong> ' + lang.username_schoose_another;
                                            }
                                            if ((checkPass() == false && jQuery.trim(pwd).length > 0 && $("[name='update']").val() > 0) || (checkPass() == false && $("[name='update']").val() == 0)) {

                                                errors[2] = '<strong>' + lang.wrong_pass + '</strong> ' + lang.pass_valid_progress;
                                            }
                                            if (jQuery.trim(usr).length > 30 || jQuery.trim(fname).search(<?= $GLOBALS['CONFIG']['FULLNAMEREGEX'] ?>) === -1) {
                                                errors[3] = '<strong>' + lang.fullname_wrong + '</strong> ' + lang.fullname_validation;
                                            }
                                            if (!reg.test(jQuery.trim(email))) {
                                                errors[4] = '<strong>' + lang.invalid_email + '</strong> ' + lang.use_valid_email;
                                            }
                                            if (errors.length > 0) {
                                                $("#reg-errors").show();
                                                for (i = 0; i < errors.length; i++) {
                                                    if (i == 0)
                                                        $("#reg-errors").empty();
                                                    if (typeof errors[i] !== 'undefined') {
                                                        $("#reg-errors").append(errors[i] + "<br>");
                                                    }
                                                }
                                                $('.modal-open .modal').scrollTop(0);
                                            } else {
                                                document.getElementById("user-add").submit();
                                            }

                                        }
                                        $('#addUser').on('hidden.bs.modal', function () {
                                            document.getElementById("user-add").reset();
                                            checkPass()
                                            $("#prof-list, #add-new-prof").show();
                                            $("#add-new-prof-field, #close-prof-list").hide();
                                            $("[name='new-prof']").val(0);
                                            $("#custom-privileges-cancel, #privileges-types").hide();
                                            $("#privileges, #custom-privileges").show();
                                            $("[name='costom-privileges']").val(0);
                                            $("[name='update']").val(0);
                                            $("#reg-errors").hide();
                                            $("#cover").hide();
                                            $("[name='username']").prop('disabled', false);
                                            $("#GeneratePwd").text(lang.generate_pass);
                                            $("[name='lang']").prop("disabled", true);
                                            $('#default_lang').prop("checked", true);
                                        });
                                        function editUser(id) {
                                            $.ajax({
                                                type: "POST",
                                                url: "<?= base_url('getuserinfo') ?>",
                                                data: {uid: id}
                                            }).done(function (data) {
                                                var info = JSON.parse(data);
                                                var projects = info.projects.split(",");
                                                var privileges = info.privileges.split(",");
                                                $('#addUser').modal('show');
                                                $("[name='update']").val(id);
                                                $("[name='username']").val(info.username);
                                                $("[name='username']").prop('disabled', true);
                                                $("#GeneratePwd").text(lang.generate_new_pass);
                                                $("[name='fullname']").val(info.fullname);
                                                $("#cover").attr('src', '<?= base_url() . $GLOBALS['CONFIG']['IMAGESUSERSUPLOADDIR'] ?>' + info.image);
                                                $("#cover").show();
                                                $("[name='facebook']").val(info.social.facebook);
                                                $("[name='twitter']").val(info.social.twitter);
                                                $("[name='linkedin']").val(info.social.linkedin);
                                                $("[name='skype']").val(info.social.skype);
                                                $("[name='email']").val(info.email);
                                                if (info.lang != null) {
                                                    $("[name='lang']").prop("disabled", false);
                                                    $('#default_lang').prop("checked", false);
                                                    $("[name='lang'] option").filter(function () {
                                                        return $(this).text() == info.lang;
                                                    }).prop('selected', true);
                                                }
                                                $("#prof-list option").filter(function () {
                                                    return $(this).text() == info.profession_name;
                                                }).prop('selected', true);
                                                $(".proj-checkbox").filter(function () {
                                                    return info.projects.contains(this.value);
                                                }).prop('checked', true);
                                                var priv_cust = true;
                                                $("#privileges option").filter(function () {
                                                    if (this.value == info.privileges) {
                                                        priv_cust = false;
                                                    }
                                                    return this.value == info.privileges;
                                                }).prop('selected', true);
                                                if (priv_cust == true) {
                                                    $("#custom-privileges-cancel, #privileges-types").show();
                                                    $("#privileges, #custom-privileges").hide();
                                                    $("[name='costom-privileges']").val(1);
                                                    $(".usrtype-checkbox").filter(function () {
                                                        return info.privileges.contains(this.value);
                                                    }).prop('checked', true);
                                                }
                                            });
                                        }

                    </script>
                    <?php
                } elseif (url_segment(1) !== false && url_segment(1) == 'projects') {
                    if (isset($_GET['delete_project'])) {
                        $this->deleteProject($_GET['delete_project']);
                        redirect('projects');
                    }
                    $projects = $this->getProjects(null, true);
                    $sync_connections = $this->getSyncInfo();
                    ?>
                    <h1><?= $this->lang_php['projects'] ?>:</h1>
                    <hr>
                    <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#createModal">
                        <?= $this->lang_php['add_project'] ?>
                    </button>
                    <hr>
                    <div id="result-ajax"></div>
                    <?php if (!empty($projects)) { ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?= $this->lang_php['abbrevation'] ?></th>
                                        <th><?= $this->lang_php['project_name'] ?></th>
                                        <th><?= $this->lang_php['time_created'] ?></th>
                                        <th><?= $this->lang_php['num_of_tickets'] ?></th>
                                        <th><?= $this->lang_php['users'] ?></th>
                                        <th class="text-center"><?= $this->lang_php['remove'] ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $proj) { ?>
                                        <tr>
                                            <td>
                                                <span class="the-abbr-<?= $proj['id'] ?>"><?= $proj['abbr'] ?></span>
                                                <input type="text" value="<?= $proj['abbr'] ?>" class="input-abbr-<?= $proj['id'] ?>" style="display:none;"> 
                                                <a href="javascript:void(0);" id="edit-abbr-<?= $proj['id'] ?>" onclick="editAbbr(<?= $proj['id'] ?>)" class="pull-right"><span class="glyphicon glyphicon-pencil"></span></a>
                                                <a href="javascript:void(0);" style="display:none;" id="save-edit-abbr-<?= $proj['id'] ?>" onclick="saveEditAbbr(<?= $proj['id'] ?>)" class="pull-right"><span class="glyphicon glyphicon-floppy-disk"></span></a>
                                            </td>
                                            <td>
                                                <span class="the-name-<?= $proj['id'] ?>"><?= $proj['name'] ?></span>
                                                <input type="text" value="<?= $proj['name'] ?>" class="input-name-<?= $proj['id'] ?>" style="display:none;"> 
                                                <a href="javascript:void(0);" id="edit-name-<?= $proj['id'] ?>" onclick="editName(<?= $proj['id'] ?>)" class="pull-right"><span class="glyphicon glyphicon-pencil"></span></a>
                                                <a href="javascript:void(0);" style="display:none;" id="save-edit-name-<?= $proj['id'] ?>" onclick="saveEditName(<?= $proj['id'] ?>)" class="pull-right"><span class="glyphicon glyphicon-floppy-disk"></span></a>
                                            </td>
                                            
                                            <td><?= date(PROJECTS_TIME_CREATED, $proj['timestamp']) ?></td>
                                            <td><a href="<?= base_url('tickets/' . $proj['name'] . '/dashboard') ?>"><?= $this->lang_php['open_dash'] ?> (<?= $proj['num_tickets'] < MAX_NUM_NOTIF ? $proj['num_tickets'] : MAX_NUM_IN_BRECKETS . '+' ?>)</a></td>
                                            <td><a href="<?= base_url('settings/profiles?for_proj=' . $proj['id']) ?>"><?= $this->lang_php['see_list'] ?> (<?= $proj['num_users'] < MAX_NUM_NOTIF ? $proj['num_users'] : MAX_NUM_IN_BRECKETS . '+' ?>)</a></td>
                                            <td class="bg-danger text-center"><a href="<?= base_url('settings/projects?delete_project=' . $proj['id']) ?>" onclick="return confirm('<?= $this->lang_php['deleting_project'] ?>')"><span class="glyphicon glyphicon-remove"></span></a></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else {
                        ?>
                        <div class="alert alert-danger"><?= $this->lang_php['no_projects'] ?></div>
                        <?php
                    }
                    ?>
                    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel"><?= $this->lang_php['add_new_project'] ?></h4>
                                </div>
                                <div class="modal-body">
                                    <div id="result"></div>
                                    <input type="hidden" value="0" name="update">
                                    <div class="form-group">
                                        <label for="title"><?= $this->lang_php['name'] ?>:</label>
                                        <input type="text" class="form-control" name="title" placeholder="<?= $this->lang_php['name_regex'] ?>" value="" id="name">
                                    </div>
                                    <div class="form-group">
                                        <label for="abbr"><?= $this->lang_php['abbrevation'] ?>:</label>
                                        <input type="text" class="form-control" name="abbr" placeholder="<?= $this->lang_php['abbrevation_regex'] ?>" maxlength="10" value="" id="abbr">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= $this->lang_php['close'] ?></button>
                                    <button type="button" class="btn btn-primary" onclick="setProject('../add_project')"><?= $this->lang_php['create'] ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script src="<?= base_url('assets/js/project_add.js') ?>"></script>
                    <script>
                                        function editAbbr(id) {
                                            $(".the-abbr-" + id + ", #edit-abbr-" + id).hide();
                                            $(".input-abbr-" + id + ", #save-edit-abbr-" + id).show();
                                        }
                                        function editName(id) {
                                            $(".the-name-" + id + ", #edit-name-" + id).hide();
                                            $(".input-name-" + id + ", #save-edit-name-" + id).show();
                                        }
                                        function saveEditName(id) {
                                            var sync_to = $('[data-change="' + id + '"]').val();
                                            var val_name = $(".input-name-" + id).val();
                                            var val_abbr = $(".input-abbr-" + id).val();
                                            $(".the-name-" + id).text(val_name);
                                            var res = goAjax(val_name, val_abbr, id);
                                            if (res == true) {
                                                $(".the-name-" + id + ", #edit-name-" + id).show();
                                                $(".input-name-" + id + ", #save-edit-name-" + id).hide();
                                            }
                                        }
                                        function saveEditAbbr(id) {
                                            var sync_to = $('[data-change="' + id + '"]').val();
                                            var val_name = $(".input-name-" + id).val();
                                            var val_abbr = $(".input-abbr-" + id).val();
                                            $(".the-abbr-" + id).text(val_abbr);
                                            var res = goAjax(val_name, val_abbr, id);
                                            if (res == true) {
                                                $(".the-abbr-" + id + ", #edit-abbr-" + id).show();
                                                $(".input-abbr-" + id + ", #save-edit-abbr-" + id).hide();
                                            }
                                        }
                                        function editSync(id) {
                                            var sure = confirm('Are you sure?');
                                            if (sure) {
                                                var sync_to = $('[data-change="' + id + '"]').val();
                                                var val_name = $(".input-name-" + id).val();
                                                var val_abbr = $(".input-abbr-" + id).val();
                                                goAjax(val_name, val_abbr, sync_to, id);
                                            }
                                        }
                                        function goAjax(name, abbr, sync_to, id) {
                                            var output;
                                            $("#loading").show();
                                            $.ajax({
                                                type: "POST",
                                                async: false,
                                                url: "<?= base_url('add_project') ?>",
                                                data: {name: name, abbr: abbr, sync_to: sync_to, update: id}
                                            }).done(function (result) {
                                                $("#loading").hide();
                                                $("#result-ajax").empty();
                                                $("#result-ajax").show();
                                                if (result > 0) {
                                                    $("#result-ajax").append('<div class="alert alert-success">' + lang.project_add_is + ' ' + lang.updated + ' ' + lang.project_add_succ + '</div>');
                                                    output = true;
                                                } else if (result == 0)
                                                {
                                                    $("#result-ajax").append('<div class="alert alert-danger">' + lang.problem_with_db + '</div>');
                                                } else {
                                                    $("#result-ajax").append('<div class="alert alert-danger">' + result + '</div>');
                                                }
                                                $("#result-ajax").delay(3000).fadeOut(1000);
                                            });
                                            return output;
                                        }
                    </script>
                    <div id="loading">Loading..</div>   
                <?php }
                ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $("#debugmode").addClass('pull-right');
        $("[data-toggle='tooltip']").tooltip();
        $('footer').remove();
    });
    $('[name="settings_search"]').keyup(function (e) {
        var search_q = $(this).val();
        if (jQuery.trim(search_q).length > 0) {
            $("#settings form.form-s img.s-loading").show();
            $.ajax({
                type: "POST",
                url: "<?= base_url('settings_search') ?>",
                data: {find: search_q}
            }).done(function (data) {
                if (data != 0) {
                    $("#settings-query-result").empty().show().append(data);
                } else {
                    $("#settings-query-result").empty().show().append('<a class="list-group-item select-suggestion" style="background-color:#f2dede !important;">' + lang.there_are_no_results + '</a>');
                }
                $("#settings form.form-s img.s-loading").hide();
            });
        } else {
            $("#settings-query-result").empty().hide();
        }
    });
    $(document).click(function (e) {
        $('#settings-query-result').empty().hide();
    });
    function load_cke() {
        if ($("#cke_description")) {
            $("#cke_description").remove();
        }
        var hEd = CKEDITOR.instances['description'];
        if (hEd) {
            CKEDITOR.remove(hEd);
        }
        CKEDITOR.replace('description');
    }
</script>
