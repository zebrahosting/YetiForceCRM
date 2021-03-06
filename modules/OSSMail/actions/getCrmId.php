<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

class OSSMail_getCrmId_Action extends Vtiger_Action_Controller
{

	public function checkPermission(Vtiger_Request $request)
	{
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName) . ' ' . vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request)
	{
		$adb = PearDatabase::getInstance();
		$metod = $request->get('metod');
		$params = $request->get('params');
		$params['folder'] = urldecode($params['folder']);
		$return = false;
		if (Vtiger_Functions::getModuleId('OSSMailView') && Vtiger_Functions::getModuleId('OSSMailScanner')) {
			$return = 0;
			$account = OSSMail_Record_Model::get_account_detail_by_name($params['username']);

			$result = $adb->pquery("SELECT ossmailviewid FROM vtiger_ossmailview WHERE id = ? AND mbox = ? AND rc_user = ?", [$params['uid'], $params['folder'], $account['user_id']]);
			if ($adb->num_rows($result) > 0) {
				$return = $adb->query_result($result, 0, 'ossmailviewid');
			} else if (isset($account['actions'])) {
				$scannerModel = Vtiger_Record_Model::getCleanInstance('OSSMailScanner');
				$return = $scannerModel->manualScanMail($params);
			}
		}
		$response = new Vtiger_Response();
		$response->setResult($return);
		$response->emit();
	}
}
