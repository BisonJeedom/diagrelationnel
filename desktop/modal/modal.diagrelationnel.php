<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}

if (substr_compare(init('src'), '.png', -strlen('.png')) !== 0) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
if (strpos(init('src'), 'diagrelationnel') === false) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<div class="slidecontainer" style="width: 200px;">
  <input type="range" min="30" max="600" value="100" class="slider" id="myRange" style="background-color: #a7abab !important;">
</div>

<?php
echo '<center><img id="img-id" class="img-responsive" src="' . init('src') . '" style="background: var(--objectTxt-color); max-width: 600%; width: 100%; margin-top: 15px" /></center>';
?>

<style>
  .slider {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 15px;
    border-radius: 5px;
    outline: none;
    opacity: 0.7;
    -webkit-transition: .2s;
    transition: opacity .2s;
  }

  .slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    background: #15ebeb !important;
    cursor: pointer;
  }

  .slider::-moz-range-thumb {
    width: 25px;
    height: 25px;
    border-radius: 50%;
    background: var(--al-info-color);
    cursor: pointer;
  }
</style>

<script>
  var slider = document.getElementById("myRange");
  slider.oninput = function() {
    document.getElementById("img-id").style.width = this.value + "%";
  }
</script>