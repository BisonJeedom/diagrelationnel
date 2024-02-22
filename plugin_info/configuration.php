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


require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
  include_file('desktop', '404', 'php');
  die();
}
$colors = array('aliceblue', 'antiquewhite', 'aquamarine', 'azure', 'beige', 'bisque', 'blue', 'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate', 'coral', 'cornsilk', 'crimson', 'cyan', 'darkgoldenrod', 'darkorange', 'darkorchid', 'darksalmon', 'darkseagreen', 'darkslateblue', 'darkviolet', 'deeppink', 'deepskyblue', 'dodgerblue', 'firebrick', 'floralwhite', 'forestgreen', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'gray', 'green', 'greenyellow', 'honeydew', 'hotpink', 'indianred', 'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue', 'lightcoral', 'lightcyan', 'lightgray', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue', 'lightslategray', 'lightsteelblue', 'lightyellow', 'limegreen', 'linen', 'magenta', 'maroon', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise', 'mediumvioletred', 'mistyrose', 'moccasin', 'navajowhite', 'oldlace', 'olivedrab', 'orange', 'orangered', 'orchid', 'palegoldenrod', 'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'purple', 'red', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna', 'skyblue', 'slateblue', 'slategray', 'snow', 'springgreen', 'steelblue', 'tan', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat', 'white', 'whitesmoke', 'yellow', 'yellowgreen');

?>
<form class="form-horizontal">
  <fieldset>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Couleur du scénario qui est dans le groupe}}</label>
      <div class="col-md-4">
        <input id="input_ingroup_color" type="text" style="background-color: <?php config::byKey('cfg_ingroup_color', 'diagrelationnel', 'mediumturqoise') ?> ;" class="configKey eqLogicAttr form-control" data-l1key="cfg_ingroup_color" placeholder="mediumturqoise" readonly />
      </div>
      <div class="col-md-4">
        <div id="sel_ingroup_color" class="sel">
          <div id="label_ingroup_color" class="label">Selectionner une autre couleur</div>
          <div id="options_ingroup_color" class="options">
            <?php
            foreach ($colors as $color) {
              echo '<div style="background-color: ' . $color . ';">' . $color . '</div>';
            }
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Couleur des actions}}</label>
      <div class="col-md-4">
        <input id="input_action_color" type="text" class="configKey eqLogicAttr form-control" data-l1key="cfg_action_color" placeholder="wheat" readonly />
      </div>
      <div class="col-md-4">
        <div id="sel_action_color" class="sel">
          <div id="label_action_color" class="label">Selectionner une autre couleur</div>
          <div id="options_action_color" class="options">
            <?php
            foreach ($colors as $color) {
              echo '<div style="background-color: ' . $color . ';">' . $color . '</div>';
            }
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Couleur des plugins}}</label>
      <div class="col-md-4">
        <input id="input_plugin_color" type="text" class="configKey eqLogicAttr form-control" data-l1key="cfg_plugin_color" placeholder="palegreen" readonly />
      </div>
      <div class="col-md-4">
        <div id="sel_plugin_color" class="sel">
          <div id="label_plugin_color" class="label">Selectionner une autre couleur</div>
          <div id="options_plugin_color" class="options">
            <?php
            foreach ($colors as $color) {
              echo '<div style="background-color: ' . $color . ';">' . $color . '</div>';
            }
            ?>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label class="col-md-4 control-label">{{Couleur des notes}}</label>
      <div class="col-md-4">
        <input id="input_note_color" type="text" class="configKey eqLogicAttr form-control" data-l1key="cfg_note_color" placeholder="orchid" readonly />
      </div>
      <div class="col-md-4">
        <div id="sel_note_color" class="sel">
          <div id="label_note_color" class="label">Selectionner une autre couleur</div>
          <div id="options_note_color" class="options">
            <?php
            foreach ($colors as $color) {
              echo '<div style="background-color: ' . $color . ';">' . $color . '</div>';
            }
            ?>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
</form>

<script>
  // ingroup_color
  document.getElementById('options_ingroup_color').setAttribute('hidden', true);

  document.getElementById('sel_ingroup_color').addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('options_ingroup_color').removeAttribute('hidden');
  });

  document.getElementById('options_ingroup_color').addEventListener('click', (e) => {
    document.getElementById('options_ingroup_color').setAttribute('hidden', true);
  });

  document.getElementById('options_ingroup_color').addEventListener('click', (e) => {
    if (e.target.tagName === 'DIV') {
      e.stopPropagation();
      document.getElementById('label_ingroup_color').textContent = e.target.textContent;
      e.target.classList.add('selected');
      document.getElementById("input_ingroup_color").value = e.target.textContent;
      Array.from(e.target.parentNode.children).forEach((child) => {
        if (child !== e.target) {
          child.classList.remove('selected');
        }
      });
      document.getElementById('options_ingroup_color').setAttribute('hidden', true);
    }
  });

  // action_color
  document.getElementById('options_action_color').setAttribute('hidden', true);

  document.getElementById('sel_action_color').addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('options_action_color').removeAttribute('hidden');
  });

  document.getElementById('options_action_color').addEventListener('click', (e) => {
    document.getElementById('options_action_color').setAttribute('hidden', true);
  });

  document.getElementById('options_action_color').addEventListener('click', (e) => {
    if (e.target.tagName === 'DIV') {
      e.stopPropagation();
      document.getElementById('label_action_color').textContent = e.target.textContent;
      e.target.classList.add('selected');
      document.getElementById("input_action_color").value = e.target.textContent;
      Array.from(e.target.parentNode.children).forEach((child) => {
        if (child !== e.target) {
          child.classList.remove('selected');
        }
      });
      document.getElementById('options_action_color').setAttribute('hidden', true);
    }
  });

  // plugin_color
  document.getElementById('options_plugin_color').setAttribute('hidden', true);

  document.getElementById('sel_plugin_color').addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('options_plugin_color').removeAttribute('hidden');
  });

  document.getElementById('options_plugin_color').addEventListener('click', (e) => {
    document.getElementById('options_plugin_color').setAttribute('hidden', true);
  });

  document.getElementById('options_plugin_color').addEventListener('click', (e) => {
    if (e.target.tagName === 'DIV') {
      e.stopPropagation();
      document.getElementById('label_plugin_color').textContent = e.target.textContent;
      e.target.classList.add('selected');
      document.getElementById("input_plugin_color").value = e.target.textContent;
      Array.from(e.target.parentNode.children).forEach((child) => {
        if (child !== e.target) {
          child.classList.remove('selected');
        }
      });
      document.getElementById('options_plugin_color').setAttribute('hidden', true);
    }
  });

  // note_color
  document.getElementById('options_note_color').setAttribute('hidden', true);

  document.getElementById('sel_note_color').addEventListener('click', (e) => {
    e.stopPropagation();
    document.getElementById('options_note_color').removeAttribute('hidden');
  });

  document.getElementById('options_note_color').addEventListener('click', (e) => {
    document.getElementById('options_note_color').setAttribute('hidden', true);
  });

  document.getElementById('options_note_color').addEventListener('click', (e) => {
    if (e.target.tagName === 'DIV') {
      e.stopPropagation();
      document.getElementById('label_note_color').textContent = e.target.textContent;
      e.target.classList.add('selected');
      document.getElementById("input_note_color").value = e.target.textContent;
      Array.from(e.target.parentNode.children).forEach((child) => {
        if (child !== e.target) {
          child.classList.remove('selected');
        }
      });
      document.getElementById('options_note_color').setAttribute('hidden', true);
    }
  });
</script>

<style>
  * {
    box-sizing: border-box;
  }

  .sel {
    color: #000000;
    width: 250px;
    box-sizing: border-box;
    border: 1px solid #cccccc;
    border-radius: 5px;
    overflow: hidden;
    background: #ffffff url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23000000' xmlns='http://www.w3.org/2000/svg'><path d='M7.2 11.1 2.5 5.7A1 1 0 0 1 3.2 4h9.6a1 1 0 0 1 .7 1.7L8.8 11a1 1 0 0 1-1.6 0z'/></svg>") no-repeat calc(100% - 10px) 14px;
  }

  .label,
  .sel .options div {
    padding: 10px;
  }

  .label {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;

    transform: translateY(5px);
  }

  .sel .options div:hover {
    font-weight: bold;
  }
</style>