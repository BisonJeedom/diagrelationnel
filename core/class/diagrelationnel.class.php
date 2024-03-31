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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class diagrelationnel extends eqLogic {

  static function setupCron($creation) {
    if ($creation == 1) {
      // Cron à 00h30
      $oCron = cron::byClassAndFunction(__CLASS__, 'diagrelationnelCron');
      if (!is_object($oCron)) {
        $oCron = new cron();
        $oCron->setClass('diagrelationnel');
        $oCron->setFunction('diagrelationnelCron');
        $oCron->setEnable(1);
        $oCron->setSchedule('30 0 * * *');
        $oCron->setTimeout('2');
        $oCron->save();
      }
    } else {
      $oCron = cron::byClassAndFunction(__CLASS__, 'diagrelationnelCron');
      if (is_object($oCron)) {
        $oCron->remove();
      }
    }
  }

  static function diagrelationnelCron() {
    self::refreshAll();
  }

  function generate_diagram($_dsltext) {
    //define('POSTVARS', $_dsltext);
    $ch = curl_init();

    //curl_setopt($ch, CURLOPT_URL, 'https://yuml.me/diagram/scruffy/class/');
    //curl_setopt($ch, CURLOPT_URL, 'https://yuml.me/diagram/nofunky;scale:200;dir:td/class/');
    curl_setopt($ch, CURLOPT_URL, 'https://yuml.me/diagram/nofunky;dir:td/class/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_dsltext);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
      log::add(__CLASS__, 'error', curl_error($ch));
    }
    curl_close($ch);
    return ($result);
  }

  function get_diagram($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $content = curl_exec($ch);
    if ($content === false) {
      log::add(__CLASS__, 'error', __FUNCTION__ . "URL: $url Failed curl_error: (" . curl_errno($ch) . ") " . curl_error($ch));
      curl_close($ch);
      return (null);
    }
    curl_close($ch);
    return ($content);
  }

  function get_use($_sc) {
    $use = $_sc->getUse();
    $arr = array();
    foreach ($use['scenario'] as $scenarioLink) {
      if ($scenarioLink->getId() == $_sc->getId()) {
        continue;
      }
      $arr[] = $scenarioLink->getId();
    }
    return $arr;
  }

  function get_usedBy($_sc) {
    $usedBy = $_sc->getUsedBy();
    $arr = array();
    foreach ($usedBy['scenario'] as $scenarioLink) {
      if ($scenarioLink->getId() == $_sc->getId()) {
        continue;
      }
      $arr[] = $scenarioLink->getId();
    }
    return $arr;
  }

  function cleanstring($_str) {
    $str = str_replace('[', '［', $_str);
    $str = str_replace(']', '］', $str);
    $str = str_replace('&', '＆', $str);
    return $str;
  }

  function getDefinedAction($_id) {
    $return['definedAction'] = array();
    $definedAction = cmd::searchConfiguration('"scenario_id":"' . $_id . '"');
    foreach ($definedAction as $cmd) {
      $cmdArray = utils::o2a($cmd);
      foreach ($cmdArray['configuration']['actionCheckCmd'] as $actionCmd) {
        try {
          if ($actionCmd['cmd'] == 'scenario' && $actionCmd['options']['scenario_id'] == $_id) {
            $action = array(
              'cmdId' => $cmd->getId(),
              'name' => $cmd->getEqLogic()->getHumanName() . ' [' . $cmd->getName() . ']',
              'enable' => $actionCmd['options']['enable'],
              'type' => 'actionCheckCmd'
            );
            array_push($return['definedAction'], $action);
          }
        } catch (Exception $e) {
        }
      }
      if (isset($cmdArray['configuration']['jeedomPreExecCmd'])) {
        foreach ($cmdArray['configuration']['jeedomPreExecCmd'] as $actionCmd) {
          try {
            if ($actionCmd['cmd'] == 'scenario' && $actionCmd['options']['scenario_id'] == $_id) {
              $action = array(
                'cmdId' => $cmd->getId(),
                'name' => $cmd->getEqLogic()->getHumanName() . ' [' . $cmd->getName() . ']',
                'enable' => $actionCmd['options']['enable'],
                'type' => 'jeedomPreExecCmd'
              );
              array_push($return['definedAction'], $action);
            }
          } catch (Exception $e) {
          }
        }
      }
      if (isset($cmdArray['configuration']['jeedomPostExecCmd'])) {
        foreach ($cmdArray['configuration']['jeedomPostExecCmd'] as $actionCmd) {
          try {
            if ($actionCmd['cmd'] == 'scenario' && $actionCmd['options']['scenario_id'] == $_id) {
              $action = array(
                'cmdId' => $cmd->getId(),
                'name' => $cmd->getEqLogic()->getHumanName() . ' [' . $cmd->getName() . ']',
                'enable' => $actionCmd['options']['enable'],
                'type' => 'jeedomPostExecCmd'
              );
              array_push($return['definedAction'], $action);
            }
          } catch (Exception $e) {
          }
        }
      }
    }
    return $return;
  }

  function record_relation($_type, $_fromid, $_fromingroup, $_fromdesc, $_toid, $_toingroup, $_todesc) {
    // "type_relation" : Relation de type [1 : entre scénarios ; 2 : entre "Déclenchement" et scénarios ; 3 : entre "Actions de déclenchement" et scénarios]
    // "from_id" : Id de l'eqLogic l'appelant
    // "from_ingroup" : 1 si l'appelant est dans le groupe analysé, 0 sinon
    // "from_desc" : Description de l'appelant
    // "to_id" => // Id de l'eqLogic appelé
    // "to_ingroup" : 1 si l'appelé est dans le groupe analysé, 0 sinon
    // "to_desc" : Description de l'appelé
    $arr[] = array(
      "type_relation" => $_type,
      "from_id" => $_fromid,
      "from_ingroup" => $_fromingroup,
      "from_desc" => $_fromdesc,
      "to_id" => $_toid,
      "to_ingroup" => $_toingroup,
      "to_desc" => $_todesc
    );
    return $arr;
  }

  function get_desc_to_dsl($_sc) {
    // Description du scénario
    $description = $_sc->getDescription();
    //log::add(__CLASS__, 'debug', '  Description : ' . $description);
    $sDeclenchement = $description == '' ? '' : '|' . $description;
    return $this->cleanstring($sDeclenchement);
  }

  function get_declenchement_to_dsl($_sc) {
    // Déclenchements du scénario (ScheduleTrigger)
    $ScheduleTrigger = '';
    $schedules = $_sc->getSchedule();
    if ($_sc->getMode() == 'schedule' || $_sc->getMode() == 'all') {
      if (is_array($schedules)) {
        foreach ($schedules as $schedule) {
          //log::add(__CLASS__, 'debug', '    - Programmation : ' . $schedule);
          //$relations_array[] = $this->record_relation(3, 0, 0, $schedule, $_sc->getId(), $from_ingroup, '');
          $ScheduleTrigger .= $schedule . ';';
        }
      } else {
        if ($schedules != '') {
          //log::add(__CLASS__, 'debug', '    - Programmation : ' . $schedules);
          //$relations_array[] = $this->record_relation(3, 0, 0, $schedules, $_sc->getId(), $from_ingroup, '');
          $ScheduleTrigger .= $schedules . ';';
        }
      }
    }
    if ($_sc->getMode() == 'provoke' || $_sc->getMode() == 'all') {
      foreach (($_sc->getTrigger()) as $trigger) {
        if ($trigger != '') {
          //log::add(__CLASS__, 'debug', '    - Evènement : ' . jeedom::toHumanReadable($trigger));
          //$relations_array[] = $this->record_relation(3, 0, 0, jeedom::toHumanReadable($trigger), $_sc->getId(), $from_ingroup, '');
          $ScheduleTrigger .= jeedom::toHumanReadable($trigger) . ';';
        }
      }
    }
    //log::add(__CLASS__, 'debug', '---- ScheduleTrigger (avant) : ' . $ScheduleTrigger);
    if ($ScheduleTrigger != '') {
      $ScheduleTrigger = substr($ScheduleTrigger, 0, -1); // Suppression du dernière caractère (point-virgule);
      $ScheduleTrigger = '|' . $ScheduleTrigger;
      //$from_desc .= $ScheduleTrigger;
    }
    //log::add(__CLASS__, 'debug', '---- ScheduleTrigger (après) : ' . $ScheduleTrigger);
    //log::add(__CLASS__, 'debug', '---- ScheduleTrigger : ' . $ScheduleTrigger);
    return $this->cleanstring($ScheduleTrigger);
  }

  public function get_desc_and_declenchement($_id, $_array) {
    $array = $_array;
    $from_sc = scenario::byId($_id);
    if (!in_array($_id, $_array['id'])) {
      log::add(__CLASS__, 'debug', '  Traitement de la description et des declenchements');
      $desc_dsl = $this->get_desc_to_dsl($from_sc); // Récupération description pour construire dsl
      $declenchement_dsl = $this->get_declenchement_to_dsl($from_sc); // Récupération déclenchement pour construire dsl
      log::add(__CLASS__, 'debug', '    ' . $desc_dsl);
      log::add(__CLASS__, 'debug', '    ' . $declenchement_dsl);
      $array[] = array('id' => $_id, 'desc' => $desc_dsl, 'declenchement' => $declenchement_dsl);
      log::add(__CLASS__, 'debug', '    Liste des ID dont les infos complètes ont été récupérés : ' . json_encode($_array));
    }
    return $array;
  }

  public function get_Plugin_Mode() {
    //log::add(__CLASS__, 'debug', 'Analyse du plugin Mode');
    $mode_array =  array();
    foreach (eqLogic::byType('mode') as $eqLogic) {
      $eqModeId = $eqLogic->getId();
      $eqModeName = $eqLogic->getHumanName();
      //log::add(__CLASS__, 'debug', 'eqLogic ID : ' . $eqModeId);
      //log::add(__CLASS__, 'debug', 'eqLogic Name : ' . $eqModeName);

      //$existing_mode = array();

      if (is_array($eqLogic->getConfiguration('modes'))) {
        foreach ($eqLogic->getConfiguration('modes') as $mode_key => $mode) {
          //$existing_mode[] = $mode['name'];
          //log::add(__CLASS__, 'debug', 'json value : ' . json_encode($mode));
          //log::add(__CLASS__, 'debug', 'name : ' . $mode['name']);
          if (is_array($mode['inAction'])) {
            foreach ($mode['inAction'] as $inAction_key => $inAction) {
              //$scenario->setLog('inAction key : ' . $inAction_key);
              //log::add(__CLASS__, 'debug', 'inAction cmd : ' . $inAction['cmd']);
              if ($inAction['cmd'] == 'scenario') {
                //$scenario->setLog('json inAction : ' . json_encode($inAction['options']));
                $sc_id = $inAction['options']['scenario_id'];
                //log::add(__CLASS__, 'debug', '  inAction scenario : ' . $sc_id);
                $mode_array[$sc_id][] = $eqModeId;
              }
            }
          }
          if (is_array($mode['outAction'])) {
            foreach ($mode['outAction'] as $outAction_key => $outAction) {
              //$scenario->setLog('outAction key : ' . $inAction_key);
              //log::add(__CLASS__, 'debug', 'outAction cmd : ' . $outAction['cmd']);
              if ($outAction['cmd'] == 'scenario') {
                //$scenario->setLog('json outAction : ' . json_encode($outAction['options']));
                $sc_id = $outAction['options']['scenario_id'];
                //log::add(__CLASS__, 'debug', '  outAction scenario : ' . $sc_id);
                $mode_array[$sc_id][] = $eqModeId;
              }
            }
          }
        }
      }
    }
    return $mode_array;
  }


  public function refreshAll() {
    foreach (eqLogic::byType('diagrelationnel', true) as $eqLogic) {
      if ($eqLogic->getIsEnable()) {
        $eqLogic->refreshLinks(0);
      }
    }
  }

  public function refreshLinks($_forceupdate = 0) {
    $ingroup_color = ' {bg:' . config::byKey('cfg_ingroup_color', __CLASS__, 'mediumturquoise') . '}'; // couleur des scénarios du groupe source    
    $action_color = ' {bg:' . config::byKey('cfg_action_color', __CLASS__, 'wheat') . '}'; // couleur des actions de déclenchement
    $plugin_color = ' {bg:' . config::byKey('cfg_plugin_color', __CLASS__, 'orchid') . '}'; // couleur des plugins   
    $note_color = ' {bg:' . config::byKey('cfg_note_color', __CLASS__, 'palegreen') . '}'; // couleur de la note du diagramme
    $inactive_color = ' {bg:gainsboro}'; // couleur des scénarios inactifs

    $selected_group = $this->getConfiguration('cfg_SelectedGroup');
    $excluded_group = $this->getConfiguration('cfg_ExcludedGroup');
    $cfg_checkinactive = $this->getConfiguration('cfg_checkinactive', 0);

    log::add(__CLASS__, 'info', 'excluded_group : ' . $excluded_group);

    log::add(__CLASS__, 'info', '----------------------------------------------');

    $Relations_Plugin_Mode = $this->get_Plugin_Mode();
    if (count($Relations_Plugin_Mode) > 0) {
      log::add(__CLASS__, 'debug', '*** Relation avec le plugin Mode : ' . json_encode($Relations_Plugin_Mode) . ' ***');
    }

    log::add(__CLASS__, 'info', 'Analyse de l\'équipement ' . $this->getName());


    if ($selected_group == '') {
      log::add(__CLASS__, 'warning', 'Cet équipement n\'a aucun groupe défini, abandon du traitement');
      return;
    }
    if ($selected_group == $excluded_group) {
      log::add(__CLASS__, 'warning', 'Le groupe à ne pas prendre en compte ne peut pas être le même que le groupe défini, abandon du traitement');
      return;
    }

    $sc_id_tocheck = array();
    $sc_id_excluded = array();

    log::add(__CLASS__, 'info', 'Analyse des scénarios du groupe ' . $selected_group);
    $scenarios = scenario::allOrderedByGroupObjectName();
    foreach ($scenarios as $sc) {
      if ($sc->getGroup() == $selected_group) {
        log::add(__CLASS__, 'info', '  ' . $sc->getHumanName());
        $sc_id_tocheck[] = $sc->getId();
      }
      if ($sc->getGroup() == $excluded_group) {
        log::add(__CLASS__, 'info', '  ' . $sc->getHumanName() . ' est à exclure');
        $sc_id_excluded[] = $sc->getId();
      }
    }
    log::add(__CLASS__, 'debug', 'Liste des ID : ' . json_encode($sc_id_tocheck));
    log::add(__CLASS__, 'info', '----------------------------------------------');

    $dsltext = '';
    $dsl = '';

    while (count($sc_id_tocheck) > 0) { // boucle tant qu'il y a des scénarios à parcourir
      foreach ($sc_id_tocheck as $sc_id) {
        $sc_id_checked[] = $sc_id;
        array_shift($sc_id_tocheck); // Retire le premier élément du tableau pour ne pas analyser le scénario à la prochaine boucle

        $from_sc = scenario::byId($sc_id);
        $from_sc_islinked = 0;

        log::add(__CLASS__, 'debug', 'Check ID ' . $sc_id . ' : ' . $from_sc->getHumanName());
        log::add(__CLASS__, 'debug', '  Description et declenchements du scénario source :');

        $from_desc_dsl = $this->get_desc_to_dsl($from_sc); // Récupération description pour construire dsl
        $from_declenchement_dsl = $this->get_declenchement_to_dsl($from_sc); // Récupération déclenchement pour construire dsl

        log::add(__CLASS__, 'debug', '    ' . $from_desc_dsl);
        log::add(__CLASS__, 'debug', '    ' . $from_declenchement_dsl);

        $from_sc_name = $this->cleanstring($from_sc->getHumanName());
        $from_ingroup = $from_sc->getGroup() == $selected_group ? 1 : 0;
        $from_color = $from_ingroup == 1 ? $ingroup_color : ''; // Colorisation de l'élément s'il est dans les scénarios du groupe
        if ($cfg_checkinactive == 1 && $from_sc->getIsActive() == 0) {
          log::add(__CLASS__, 'debug', '  Ce scénario est inactif');
          $from_color = $from_sc->getIsActive() == 0 ? $inactive_color : $from_color; // Colorisation différente de l'élément inactif
        }
        $from_dsl = '[' . $from_sc_name . $from_desc_dsl . $from_declenchement_dsl . $from_color . ']';
        //$dsl = '[' . $from_sc_name . $desc_dsl . $declenchement_dsl . $couleur_dsl . '],';
        //$relations_array[] = $this->record_relation(1, $action['cmdId'], 0, '', $from_sc->getId(), 0, '');

        log::add(__CLASS__, 'debug', '  Actions de declenchement du scénario source :');
        // Actions de déclenchement du scénario source (definedAction)
        $arr_definedAction = $this->getDefinedAction($sc_id);
        foreach ($arr_definedAction['definedAction'] as $action) {
          //log::add(__CLASS__, 'debug', '  Actions de déclenchement : ' . json_encode($action));
          if ($action['enable'] == 1) {
            $from_sc_islinked = 1;
            $definedAction_name = $this->cleanstring($action['name']);
            if ($action['type'] == 'actionCheckCmd') {
              $definedAction_type = 'Action sur valeur';
            } elseif ($action['type'] == 'jeedomPostExecCmd') {
              $definedAction_type = 'Action après éxécution';
            } elseif ($action['type'] == 'jeedomPreExecCmd') {
              $definedAction_type = 'Action avant éxécution';
            } else {
              $definedAction_type = '';
            }
            log::add(__CLASS__, 'debug', '    ' . $definedAction_name . ' de type ' . $definedAction_type);
            $dsl = $from_dsl . '^-.-' . $definedAction_type . '[' . $definedAction_name . $action_color . '],';
            //$relations_array[] = $this->record_relation(3, $action['cmdId'], 0, '', $from_sc->getId(), 0, '');
            log::add(__CLASS__, 'debug', '    >dsl : ' . $dsl);
            $dsltext .= $dsl;
          }
        }

        // Déclenchements du scénario source par le plugin Mode
        if (count($Relations_Plugin_Mode) > 0) {
          log::add(__CLASS__, 'debug', '  Déclenchement du scénario source par le plugin Mode :');
          if (isset($Relations_Plugin_Mode[$sc_id])) {
            foreach ($Relations_Plugin_Mode[$sc_id] as $mode_id) {
              //$plugin_EqName = $this->cleanstring(eqLogic::byId($Relations_Plugin_Mode[$sc_id])->getHumanName());
              $plugin_EqName = $this->cleanstring(eqLogic::byId($mode_id)->getHumanName());
              log::add(__CLASS__, 'debug', '   L\'équipement mode ' . $plugin_EqName . ' appel le scénario source');
              $dsl = $from_dsl . '^-.-' . '[' . $plugin_EqName . $plugin_color . '],';
              log::add(__CLASS__, 'debug', '    >dsl : ' . $dsl);
              $dsltext .= $dsl;
            }
          }
        }


        // Traitement des scénarios appelés par le scénario source
        $arr_use = $this->get_use($from_sc);
        $json_arr_use = empty($arr_use) ? 'Aucun' : json_encode($arr_use);
        log::add(__CLASS__, 'debug', ' Le scénario appelle ces ID : ' . $json_arr_use);
        foreach ($arr_use as $new_id_to_check) {
          if (in_array($new_id_to_check, $sc_id_excluded)) {
            log::add(__CLASS__, 'debug', '  ! Par paramétrage le scénario ' . $new_id_to_check . ' est dans un groupe à ne pas prendre en compte');
            continue;
          }
          $from_sc_islinked = 1;
          $to_sc = scenario::byId($new_id_to_check);
          log::add(__CLASS__, 'debug', '  Description et declenchements du scénario cible :');
          $to_desc_dsl = $this->get_desc_to_dsl($to_sc); // Récupération description pour construire dsl
          $to_declenchement_dsl = $this->get_declenchement_to_dsl($to_sc); // Récupération déclenchement pour construire dsl
          log::add(__CLASS__, 'debug', '    ' . $to_desc_dsl);
          log::add(__CLASS__, 'debug', '    ' . $to_declenchement_dsl);
          $to_sc_name = $this->cleanstring(scenario::byId($new_id_to_check)->getHumanName());
          $to_ingroup = scenario::byId($new_id_to_check)->getGroup() == $selected_group ? 1 : 0;
          $to_color = $to_ingroup == 1 ? $ingroup_color : '';
          if ($cfg_checkinactive == 1 && $to_sc->getIsActive() == 0) {
            log::add(__CLASS__, 'debug', '  Ce scénario est inactif');
            $to_color = $to_sc->getIsActive() == 0 ? $inactive_color : $to_color; // Colorisation différente de l'élément inactif
          }
          $to_dsl = '[' . $to_sc_name . $to_desc_dsl . $to_declenchement_dsl . $to_color . ']';

          $dsl = $from_dsl . '->' . $to_dsl . ',';
          log::add(__CLASS__, 'debug', '    >dsl : ' . $dsl);
          $dsltext .= $dsl;

          //$relations_array[] = $this->record_relation(1, $from_sc->getId(), $from_ingroup, $from_desc, $to_sc->getId(), $to_ingroup, $to_desc);
          if (!in_array($new_id_to_check, $sc_id_checked) && (!in_array($new_id_to_check, $sc_id_tocheck))) {
            $sc_id_tocheck[] = $new_id_to_check;
          }
        }

        // Traitement des scénarios qui appellent le scénario source
        $arr_usedBy = $this->get_usedBy($from_sc);
        $json_arr_usedBy = empty($arr_usedBy) ? 'Aucun' : json_encode($arr_usedBy);
        log::add(__CLASS__, 'debug', ' Le scénario est appelé par ces ID : ' . $json_arr_usedBy);
        foreach ($arr_usedBy as $new_id_to_check) {
          if (in_array($new_id_to_check, $sc_id_excluded)) {
            log::add(__CLASS__, 'debug', '  ! Par paramétrage le scénario ' . $new_id_to_check . ' est dans un groupe à ne pas prendre en compte');
            continue;
          }
          if (!in_array($new_id_to_check, $sc_id_checked) && (!in_array($new_id_to_check, $sc_id_tocheck))) {
            $sc_id_tocheck[] = $new_id_to_check;
          }
        }

        if ($from_sc_islinked == 0) {
          log::add(__CLASS__, 'debug', ' Le scénario n\'a aucun lien');
          $dsl = $from_dsl . ',';
          log::add(__CLASS__, 'debug', '    >dsl : ' . $dsl);
          $dsltext .= $dsl;
        }

        log::add(__CLASS__, 'debug', 'Liste des ID restant à parcourir : ' . json_encode($sc_id_tocheck));
        log::add(__CLASS__, 'debug', 'Liste des ID déjà parcouru : ' . json_encode($sc_id_checked));
      }
    }
    $note = '[note: Diagramme relationnel du groupe ' . $selected_group . $note_color . '],'; // Ajout d'une note au diagramme
    $dsltext = 'dsl_text=' . $note . $dsltext;
    $dsltext = substr($dsltext, 0, -1); // Suppression du dernière caractère (virgule);
    log::add(__CLASS__, 'debug', '>> dsltext : ' . $dsltext);

    //$relations = json_encode($relations_array);
    //log::add(__CLASS__, 'debug', 'relations : ' . $relations);

    if ($_forceupdate == 1) {
      log::add(__CLASS__, 'info', 'Demande de la mise à jour du diagramme relationnel');
      ///////////////////////////////////////////////
      // Modifier l'image du widget type loading ? //
      ///////////////////////////////////////////////     
      log::add(__CLASS__, 'debug', '  dsltext à envoyer : ' . $dsltext);
      $result = $this->generate_diagram($dsltext); // Génération du diagramme
      //log::add(__CLASS__, 'debug', 'result : ' . $result);

      $url = 'https://yuml.me/' . substr($result, 0, -4) . '.png';  // URL du fichier au format png      
      $response = $this->get_diagram($url); // Récupération du diagramme

      if ($response === null) {
        log::add(__CLASS__, 'error', 'Erreur lors de la récupération du diagramme à l\'adresse ' . $url);
      } else {
        $filename = $this->getId();
        $file = '/var/www/html/plugins/diagrelationnel/data/' . $filename . '.png';
        $resu = file_put_contents($file, $response);
        if ($resu === FALSE) {
          log::add(__CLASS__, 'error', 'Erreur lors de l\'écriture du fichier dans ' . $file);
        } else {
          log::add(__CLASS__, 'info', 'Mise à jour effectuée');
          $this->checkAndUpdateCmd('lastupdate', time());
          $this->checkAndUpdateCmd('linkschanged', 0);
          //$this->setConfiguration('relations', json_encode($relations_array));
          $this->setConfiguration('stored_dsltext', $dsltext);
          $this->save();
          sleep(2);
          $this->refreshWidget();
        }
      }
    } else {
      $stored_dsltext = $this->getConfiguration('stored_dsltext');
      //log::add(__CLASS__, 'debug', 'stored_dsltext : ' . $stored_dsltext);
      if ($stored_dsltext == $dsltext) {
        log::add(__CLASS__, 'debug', "Aucune modification dans le diagramme relationnel de l'équipement");
      } else {
        log::add(__CLASS__, 'info', "L'un des éléments du diagramme relationnel a été modifié depuis la dernière mise à jour de l'équipement");
        $this->checkAndUpdateCmd('linkschanged', 1);
        $this->refreshWidget();
      }
    }
  }



  /*     * *************************Attributs****************************** */

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */

  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  public static function cron() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly() {}
  */

  /*
  * Fonction exécutée automatiquement tous les jours par Jeedom
  public static function cronDaily() {}
  */

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */

  /*
   * Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
   * lors de la création semi-automatique d'un post sur le forum community
   public static function getConfigForCommunity() {
      return "les infos essentiel de mon plugin";
   }
   */

  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {
    $refresh = $this->getCmd(null, 'refresh');
    if (!is_object($refresh)) {
      $refresh = new diagrelationnelCmd();
      $refresh->setName(__('Rafraichir', __FILE__));
    }
    $refresh->setEqLogic_id($this->getId());
    $refresh->setLogicalId('refresh');
    $refresh->setType('action');
    $refresh->setSubType('other');
    $refresh->save();

    $refresh = $this->getCmd(null, 'lastupdate');
    if (!is_object($refresh)) {
      $refresh = new diagrelationnelCmd();
      $refresh->setName(__('Dernière mise à jour', __FILE__));
    }
    $refresh->setEqLogic_id($this->getId());
    $refresh->setLogicalId('lastupdate');
    $refresh->setType('info');
    $refresh->setSubType('numeric');
    $refresh->save();

    $refresh = $this->getCmd(null, 'linkschanged');
    if (!is_object($refresh)) {
      $refresh = new diagrelationnelCmd();
      $refresh->setName(__('Modification des relations', __FILE__));
    }
    $refresh->setEqLogic_id($this->getId());
    $refresh->setLogicalId('linkschanged');
    $refresh->setType('info');
    $refresh->setSubType('binary');
    $refresh->save();

    $this->refreshLinks(0);
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  /*
  * Permet de modifier l'affichage du widget (également utilisable par les commandes)
  */
  public function toHtml($_version = 'dashboard') {
    $replace = $this->preToHtml($_version); // initialise les tag standards : #id#, #name# ...

    if (!is_array($replace)) {
      return $replace;
    }

    $version = jeedom::versionAlias($_version);

    $selected_group = $this->getConfiguration('cfg_SelectedGroup');
    if ($selected_group != '') {
      $dir = '/var/www/html/plugins/diagrelationnel/data';
      $filename = $this->getId() . '.png';
      $linkschanged = $this->getCmd('info', 'linkschanged')->execCmd();
      $replace['#group_name#'] = $selected_group;
      $replace['#url#'] = 'core/php/downloadFile.php?pathfile=' . urlencode($dir . '/' . $filename);
      if ($this->getComment() == '') {
        $replace['#desc#'] = '';
      } else {
        $replace['#desc#'] = '<p style="border: 2px solid rgba(var(--cat-other-color), var(--opacity)); border-top: 0px; border-radius: 0px 0px 20px 20px; margin-left: 30px; margin-right: 30px">' . $this->getComment() . '</p>';
      }
      if ($this->getCmd('info', 'lastupdate')->execCmd() == '') {
        $replace['#lastupdate#'] = '';
      } else {
        $replace['#lastupdate#'] = 'Mise à jour : ' . date('d/m/Y H:i:s', $this->getCmd('info', 'lastupdate')->execCmd());
      }

      $replace['#icon_color#'] = $linkschanged == 1 ? 'icon_yellow' : '';
      $replace['#icon_tips#'] = $linkschanged == 1 ? 'Un élément du diagramme a été modifié, une mise à jour est recommandée' : 'Le diagramme est à jour';
    } else {
      $replace['#desc#'] = 'Cet objet n\'est associé à aucun groupe';
      $replace['#lastupdate#'] = '';
    }

    $getTemplate = getTemplate('core', $version, 'diagrelationnel.template', __CLASS__); // on récupère le template du plugin
    $template_replace = template_replace($replace, $getTemplate); // on remplace les tags
    $postToHtml = $this->postToHtml($_version, $template_replace); // on met en cache le widget, si la config de l'user le permet
    return $postToHtml; // renvoie le code du template

  }

  /*     * **********************Getteur Setteur*************************** */
}

class diagrelationnelCmd extends cmd {
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array()) {
    /** @var diagrelationnel $eqlogic */
    $eqlogic = $this->getEqLogic();
    switch ($this->getLogicalId()) {
      case 'refresh':
        log::add('diagrelationnel', 'debug', 'Mise à jour du diagramme relationnel de ' . $eqlogic->getName());
        $eqlogic->refreshLinks(1);
        break;

      default:
        log::add('diagrelationnel', 'debug', 'Erreur durant execute');
        break;
    }
  }

  /*     * **********************Getteur Setteur*************************** */
}
