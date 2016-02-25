<?php 
require_once ("php/config.php"); 
?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="<?php echo SITE_ROOT; ?>/rla.css">
        <!--Replace this with a web link when the site goes live.-->
        <script src="<?php echo SITE_ROOT; ?>/js/jquery-2.1.4.min.js"></script>
        <script src="index.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/achievements.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/actions.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/error.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/listings.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/profile.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/requirements.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/notes.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/tags.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/work.js"></script>
        <script src="<?php echo SITE_ROOT; ?>/js/relations.js"></script>
        <!--<script src="rla.js"></script>-->
        <style>
            .delete_button{
                //background-color: blue;
            }
        </style>
        <title><?PHP echo SITE_NAME ?></title>
    </head>

    <?php
    $rla = isset($_GET['rla']) ? filter_input(INPUT_GET, 'rla', FILTER_SANITIZE_NUMBER_INT) : 0;

    if ($rla == 0):
        ?>
        <body id="AchievementsList">
            <div id="error"></div>
            <div>
                <input id="new_achievement_text_input" type='text' maxlength="255" />          
                <input id="new_achievement_button" type="button" value="Quick Create" />
            </div>
            <div>
                <input id="hide_achievements_button" type='button' value='Hide'  />
                <input id="show_achievements_button" type='button' value='Show' style="display:none" />
                <span>Total: <span id="achievement_count"><span id="achievement_total"></span>
                        ([
                        <span id="working_total" style='color:green'></span> + 
                        <span id="quality_total" style='color:gray;'></span>]/
                        <span id="nonworking_total" style='color:red'></span>)</span></span>
                <span class="hand text-button">[ Filter ]</span>
                <span class="hand">[ Hide Filter ]</span>
            </div>
            <div id="filter_menu">
                Tags:<span id="list_of_filter_tags"></span>
            </div>

            <!--
                        <span id="sorting_menu">
                            <input id="sort_rank_button" class="sort_button" type="button" value="Rank &#8595;"/>
                            <input id="sort_rankrev_button" class="sort_button"  type='button' value="Rank &#8593;"  style="display:none" />
                            <input id="sort_power_button" class="sort_button"  type="button" value="Power &#8595;" />
                            <input id="sort_powerrev_button" class="sort_button"  type="button" value="Power &#8593;" style="display:none"/>
                            <input id="sort_name_button"  class="sort_button" type="button" value="Name &#8595;" />
                            <input id="sort_namerev_button"  class="sort_button" type="button" value="Name &#8593;"  style="display:none"/>
                            <input id="sort_created_button"  class="sort_button" type="button" value="Time &#8595;" />
                            <input id="sort_createdrev_button"  class="sort_button" type="button" value="Time &#8593;"  style="display:none"/>
                        </span>
            -->
            <div id="list_of_achievements"></div>
        <?php elseif ($rla > 0): ?>
        <body id="achievement_number_<?php echo $rla; ?>" >
            <div id="error"></div>
            <div id="achievement_profile"></div>
        <?php endif; ?>

    </body>
</html>
