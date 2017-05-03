<?php
/**
 * Plugin Name: WLP Generator Functions
 * Plugin URI: http://missionobesity.org/
 * Description: Adds custom code to wlp generator
 * Author: Nathan Moondi
 * Author URI: http://missionobesity.org/
 * Version: 1.0
 * Text Domain: http://missionobesity.org/
 *
 * Copyright: (c) 2012-2014 http://missionobesity.org/
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author    NMoondi
 * @copyright Copyright (c) 2012-2015, Mission Obesity
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 */

$DailyCalories = 1;
$CalorieMin = 0;
$CalorieMax = 0;
$TimeEstimate=0;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function getv($key, $default = '', $data_type = '')
{
    $param = (isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default);

    if (!is_array($param) && $data_type == 'int') {
        $param = intval($param);
    }

    return $param;
}



add_shortcode('TDEE', 'TDEE');
function TDEE()
{

global $DailyCalories;

$age = getv('2','int');
$sex = getv('1');
$weight = getv('3', 'int');
$heightfeet = getv('4','int');
$heightinches = getv('5','int');
$typeofjob = getv('20');
$numweeklyexercises = getv('19','int');
$medicalissues = getv('7');

$kg = $weight / 2.2;
$bmr =0;
$cm = ($heightfeet * 30.48) + ($heightinches * 2.54);

if ($sex == 'Male')
{
 $bmr = (10 * $kg) + (6.25 * $cm) - (5 * $age) + 5;
} else
{
 $bmr = (10 * $kg) + (6.25 * $cm) - (5 * $age) -161;
}

if ($numweeklyexercises == 0) { $bmr = $bmr * 1.2;}
if ($numweeklyexercises == 1) { $bmr = $bmr * 1.3;}
if ($numweeklyexercises == 2) { $bmr = $bmr * 1.6;}
if ($numweeklyexercises >= 3) { $bmr = $bmr * 1.9;}

if ($medicalissues == 'Yes') { $bmr = $bmr - 100;}
if ($typeofjob == 'Middle') { $bmr = $bmr - 50;}
if ($typeofjob == 'Office') { $bmr =  $bmr - 100;}
if ($typeofjob == 'Traveling') { $bmr = $bmr + 100;}
if ($typeofjob == 'Middle') { $bmr = $bmr - 50;}
if ($typeofjob == 'Physical') { $bmr = $bmr + 200;}

$DailyCalories = round($bmr, 2);

return $DailyCalories;
}

add_shortcode('CalorieDeficitMin', 'CalorieDeficitMin');
function CalorieDeficitMin()
{
$weight = getv('3', 'int');
$sex = getv('1');
$min = 500;

if (($weight >= 230) && ($sex == 'Male'))
{ $min = 700; }

if (($weight >= 200) && ($sex == 'Female'))
{ $min = 700; }

return $min;

}

add_shortcode('CalorieDeficitMax', 'CalorieDeficitMax');
function CalorieDeficitMax()
{
$weight = getv('3', 'int');
$sex = getv('1');
$min = CalorieDeficitMin();
$max = 700;

if (($weight >= 230) && ($sex == 'Male'))
{ $max = 900; }

if (($weight >= 250) && ($sex == 'Female'))
{ $max = 900; }

if (($weight >= 200) && ($sex == 'Female') && ($weight < 250))
{ return ' '; }
else
{ return '-' . $max; }

}

add_shortcode('TargetCalories', 'TargetCalories');
function TargetCalories()
{

$deficit = CalorieDeficitMin();
$max = TDEE();


$target = ($max - $deficit);

return round($target);
}

add_shortcode('TimeEstimate', 'TimeEstimate');
function TimeEstimate()
{
$weight = getv('3', 'int');
$goalweight = getv('22', 'int');
$deficit = CalorieDeficitMin();
$v = '';

if ($goalweight == '') { return '<p>A goal weight was not specified so we can not calculate approximate time to lose your weight.  For the non-obese, 1lb a week of weight loss is easy to attain.</p>'; }

if ($goalweight >= $weight) { return '<p>Goal weight is the same as your current weight.  Tsk tsk.</p>'; }

$lbs = $weight - $goalweight;

$calories = $lbs * 3500;

$days = ($calories / $deficit);

$weeks = $days / 6;

$weeks = $weeks+ 4;

$value = '<p>According to the goal weight you specified (' . $weight . '->' . $goalweight . 'lbs).  We estimate it would take you <strong>' . round($weeks) . ' weeks </strong> to lose that weight.  This includes 4 weeks at the end for your final weight to be lost.  Do note, it may take you a SHORTER time as well depending on your exercise level and changing calorie deficits.</p>';

return $value;

}

