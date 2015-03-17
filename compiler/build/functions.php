<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file contains the functions of the compiler.
 *
 * Include this in your code for autocompletion.
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2015 Wigger Boelens
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Wigger Boelens <wigger.boelens@gmail.com>
 * @copyright 2015 Wigger Boelens
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version   v0.2
 */

/**
 * Store a value in the ram.
 *
 * Example: _storeRam($location,$value)
 *
 * @param variable $location The location to store the value in the ram
 * @param variable $value The value to store
 *
 * @return void
 */
function _storeRam($location, $value)
{
}

/**
 * Get a value from the ram.
 *
 * Example: $value=_getRam($location)
 *
 * @param variable $location The location where the value is stored
 *
 * @return mixed The value that is stored at the location
 */
function _getRam($location)
{
}

/**
 * Display something on either the display or the leds
 *
 * Possible values for $onwhat:
 * leds: the leds at the top
 * leds2: the leds to the right
 * display: the display
 * Example:
 * display($value, 'display',000100)
 * This will display $value in the middle of the display
 *
 * @param variable $what what to display
 * @param variable $onWhat on what to display
 * @param string $location Where to show the value when using the display,
 *                           defaults to the right position
 *
 * @return void
 */
function display($what, $onWhat, $location = '000001')
{
}

/**
 * Take the mod of a number
 *
 * Example: modulo($variable,2)
 * This will return the mod 2 of $variable
 *
 * @param variable $variable variable to modulo over
 * @param int $what modulo what
 *
 * @return void
 */
function modulo($variable, $what)
{
}

/**
 * Get button or analog input
 *
 * When you just want hte input of 1 button, use buttonPressed instead
 * Example: getInput($variable,'analog')
 * This will put the value of the analog into $variable
 *
 * @param variable $writeTo Variable to write the input to
 * @param string $type Type of input, possible values are: buttons, analog
 *
 * @return void
 */
function getInput($writeTo, $type)
{
}

/**
 * Check if a button is pressed
 *
 * Puts the result into R5
 * Example:buttonPressed($location);
 * if (R5 == 1) {}
 *
 * @param variable $button Which button to check
 *
 * @return void
 */
function buttonPressed($button)
{
}

/**
 * Install the countdown
 *
 * Do not forget to add returnt at the end of the interrupt function
 * Example: installCountdown('timerInterrupt')
 * This will install the countdown.
 * In this example when the timer interrupt triggers,
 * the function timerInterrupt is ran.
 *
 * @param string $functionName The name of the function where the timer should go to
 *
 * @return void
 */
function installCountdown($functionName)
{
}

/**
 *Start the countdown.
 *
 * @return void
 */
function startCountdown()
{
}

/**
 *Push a variable to the stack
 *
 * @param string $variable the variable to push to the stack
 *
 * @return void
 */
function pushStack($variable)
{
}

/**
 *Pull a variable from the stack
 *
 * @param string $variable the variable where the pulled variable is put into
 *
 * @return void
 */
function pullStack($variable)
{
}

/**
 * Set the timer interrupt to a value.
 *
 * It will first reset the timer to 0.
 * Example: setTimer(10)
 * This will interrupt the program after 10 timer ticks
 *
 * @param string $timer how long the timer should wait, in timer ticks
 *
 * @return void
 */
function setTimer($timer)
{
}


/**
 * Get data
 *
 * Use offset 0 when it is just a single value.
 * Example: $data=_getData('data',1)
 * This will put the value of the data segment "data" at position 1, into $data.
 *
 * @param string $location The location where the variable is stored
 * @param int $offset The offset of the location
 *
 * @return mixed The value of the data segment
 */
function _getData($location, $offset)
{
}

/**
 * Store data
 *
 * Use offset 0 when it is just a single value.
 * Example: _storeData($data,'data',1)
 * This will put the value of $data into the data segment "data" at position 1
 *
 * @param string $variable The variable to store
 * @param string $location The name of the location where the variable is stored
 * @param int $offset The offset of the location
 *
 * @return void
 */
function _storeData($variable, $location, $offset)
{
}


/**
 * Pause the program
 *
 * Example:
 * sleep(10)
 * This will sleep for 10 clockticks
 *
 * @param int $howLong How long to sleep
 *
 * @return void
 */
function sleep($howLong)
{
}