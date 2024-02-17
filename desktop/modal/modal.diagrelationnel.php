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
<script src="plugins/diagrelationnel/3rdparty/js-image-zoom.js" type="application/javascript"></script>

<?php
echo '<div id="img-container" style="width: 50%"; height: 50%">';
echo '<img id="imageid" class="img-responsive" src="' . init('src') . '" style="background: var(--objectTxt-color); max-width: 100%; max-height: 100%;" />';
echo '</div>';
?>

<script>




getImageSize($('#imageid'), function(width, height) {
    //console.log(width + ',' + height)
    if (width/height > 1) {
        new ImageZoom(document.getElementById("img-container"), options_bas); // Affichage en bas
    } else {
        new ImageZoom(document.getElementById("img-container"), options_droite); // affichage à droite
    }
});

function getImageSize(img, callback) {
    var $img = $(img);

    var wait = setInterval(function() {
        var w = $img[0].clientWidth,
            h = $img[0].clientHeight;
        if (w && h) {
            clearInterval(wait);
            callback.apply(this, [w, h]);
        }
    }, 30);
}

var options_bas = {
    scale:1.8,
    zoomPosition: "bottom",
    offset: {vertical: 10, horizontal: 20},
    zoomStyle: "opacity: 1; background-color: var(--objectTxt-color);"
};

var options_droite = {
    //scale:1.3,
    zoomWidth: 700,
    zoomPosition: "right",
    offset: {vertical: 0, horizontal: 10},
    zoomStyle: "opacity: 1; background-color: var(--objectTxt-color);"
};
//new ImageZoom(document.getElementById("img-container"), options1);

</script>