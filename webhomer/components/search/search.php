<?php
/*
 * HOMER Web Interface
 * Homer's homer.php
 *
 * Copyright (C) 2011-2012 Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Copyright (C) 2011-2012 Lorenzo Mangani <lorenzo.mangani@gmail.com>
 *
 * The Initial Developers of the Original Code are
 *
 * Alexandr Dubovikov <alexandr.dubovikov@gmail.com>
 * Lorenzo Mangani <lorenzo.mangani@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
*/

require_once ('search.html.php');

class Component {


          function executeComponent() {
                    
                    global $task;                    
                    
                    //Go if all ok
                    switch ($task) {
                    
                          case 'search':
                              $this->showSearchForm(0);
                              break;            	
		
                          case 'result':
                              $this->showResultSearch();
                              break;
        	                  		
                          case 'showmessage':
                              $this->showMessage();
                              break;

                          case 'showcallflow':
                              $this->showCallFlow();
                              break;                              
             
                          default:
                 	      $this->showSearchForm(0);
                   	      break;    
                   }
          }

          function showSearchForm($type = null) {
        
                  global $mynodesname;
                  $search = array();

                  if(isset($_SESSION['homersearch'])) {
                        $search = json_decode($_SESSION['homersearch'], true);
                  }

                  if($type) HTML_search::displayAdvanceSearchForm(&$search, $mynodesname);
                  else HTML_search::displaySearchForm(&$search, $mynodesname);
          }

          function showResultSearch() {

                  global $mynodeshost, $db;
        
                  /* AJAXTYPE FIX */
                  if(!defined(AJAXTYPE)) define(AJAXTYPE, "GET");
                          
                  include('DataTable/Autoloader.php');
                  spl_autoload_register(array('DataTable_Autoloader', 'autoload'));
                  include('SipDataTable.php');
                  // instantiate the DataTable

                  $datatable = new SipDataTable();
                  // set the url to the ajax script         
                  $datatable->setAjaxDataUrl('ajax.php');                                                                                                                        
                                
                  $userparam = new stdclass();
                  $callparam = new stdclass();
                  $headerparam = new stdclass();
                  $timeparam = new stdclass();
                  $networkparam = new stdclass();
        
                  //User
                  $search['ruri_user'] = $userparam->ruri_user = getVar('ruri_user', NULL, 'post', 'string');
                  $search['to_user'] = $userparam->to_user = getVar('to_user', NULL, 'post', 'string');
                  $search['from_user'] = $userparam->from_user = getVar('from_user', NULL, 'post', 'string');
                  $search['pid_user'] = $userparam->pid_user = getVar('pid_user', NULL, 'post', 'string');
                  $search['contact_user'] = $userparam->contact_user = getVar('contact_user', NULL, 'post', 'string');
                  $search['auth_user'] = $userparam->auth_user = getVar('auth_user', NULL, 'post', 'string');
                  $search['logic_or'] = $dbic_or = getVar('logic_or', 0, 'post', 'int');
	
                  //Call	
                  $search['callid'] = $callparam->callid = getVar('callid', NULL, 'post', 'string');
                  $search['b2b'] = $b2b = getVar('b2b', 0, 'post', 'int');		
                  $search['from_tag'] = $callparam->from_tag = getVar('from_tag', NULL, 'post', 'string');
                  $search['to_tag'] = $callparam->to_tag = getVar('to_tag', NULL, 'post', 'string');
                  $search['via_1_branch'] = $callparam->via_1_branch = getVar('via_1_branch', NULL, 'post', 'string');
                  $search['method'] = $callparam->method = getVar('method', NULL, 'post', 'string');
                  $search['reply_reason'] = $callparam->reply_reason = getVar('reply_reason', NULL, 'post', 'string');
	
                  //Header
                  $search['ruri'] = $headerparam->ruri = getVar('ruri', NULL, 'post', 'string');
                  $search['via_1'] = $headerparam->via_1 = getVar('via_1', NULL, 'post', 'string');
                  $search['diversion'] = $headerparam->diversion = getVar('diversion', NULL, 'post', 'string');
                  $search['cseq'] = $headerparam->cseq = getVar('cseq', NULL, 'post', 'string');
                  $search['reason'] = $headerparam->reason = getVar('reason', NULL, 'post', 'string');
                  $search['content_type'] = $headerparam->content_type = getVar('content_type', NULL, 'post', 'string');
                  $search['authorization'] = $headerparam->authorization = getVar('authorization', NULL, 'post', 'string');
                  $search['user_agent'] = $headerparam->user_agent = getVar('user_agent', NULL, 'post', 'string');
	
                  //Time
                  $search['location'] = $location = getVar('location', array(), '', 'array');	
                  $search['from_date'] = $timeparam->from_date = getVar('from_date', '', '', 'string');	        
                  $search['to_date'] = $timeparam->to_date = getVar('to_date', '', '', 'string');	        
                  $search['from_time'] = $timeparam->from_time = getVar('from_time', NULL, '', 'string');
                  $search['to_time'] = $timeparam->to_time = getVar('to_time', NULL, '', 'string');
                  $search['max_records'] = $timeparam->max_records = getVar('max_records', 100, 'post', 'int');
                  $search['unique'] = $unique = getVar('unique', 0, 'post', 'int');

                  $ft = date("Y-m-d H:i:s", strtotime($timeparam->from_date." ".$timeparam->from_time));
                  $tt = date("Y-m-d H:i:s", strtotime($timeparam->to_date." ".$timeparam->to_time));	
                  $fhour = date("H", strtotime($timeparam->from_date." ".$timeparam->from_time));
                  $thour = date("H", strtotime($timeparam->to_date." ".$timeparam->to_time));

	        
                  //Network	        	
                  $search['source_ip'] = $networkparam->source_ip = getVar('source_ip', NULL, 'post', 'string');	
                  $search['source_port'] = $networkparam->source_port = getVar('source_port', 0, 'post', 'int');
                  $search['destination_ip'] = $networkparam->destination_ip = getVar('destination_ip', NULL, 'post', 'string');	
                  $search['destination_port'] = $networkparam->destination_port = getVar('destination_port', 0, 'post', 'int');
                  $search['contact_ip'] = $networkparam->contact_ip = getVar('contact_ip', NULL, 'post', 'string');	
                  $search['contact_port'] = $networkparam->contact_port = getVar('contact_port', 0, 'post', 'int');
                  $search['originator_ip'] = $networkparam->originator_ip = getVar('originator_ip', NULL, 'post', 'string');	
                  $search['originator_port'] = $networkparam->originator_port = getVar('originator_port', 0, 'post', 'int');
                  $datatable->setSearchRequest($search);
                  //Please change protocol
                  //$search['proto'] = $proto = getVar('proto', 2, 'post', 'int');	
                  //$search['family'] = $family = getVar('family', 2, 'post', 'int');	
                  $_SESSION['homersearch'] = json_encode($search);
                  HTML_search::displayResultSearch(&$datatable, $ft, $tt);
        }

        function showMessage()  {

                global $mynodeshost, $db;
        	$myrows = array();

                $userid = $user->id;
                
                $tnode = getVar('tnode', NULL, '', 'string');
                $location_str = getVar('location', NULL, '', 'string');
                $location = explode(",", $location_str);

                $id = getVar('id', 0, '', 'int');

                //$node = sprintf("homer_node%02d.", $tnode);

                $option = array(); //prevent problems

                if($db->dbconnect_homer("localhost")) {                
                      $query = "SELECT * FROM ".HOMER_TABLE." WHERE id=$id limit 1";
                      $rows = $db->loadObjectList($query);
                }

                HTML_search::displayMessage(&$rows);                
        }
                                
        function myLocalRedirect( $url='') {
                echo "<script>location.href='$url';</script>\n";
                exit();
        }
}

?>