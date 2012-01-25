<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */
class LeaveSummaryForm extends sfForm {

    private $formValidators = array();
    private $leavePeriodService;
    private $searchParam = array();
    private $empId;
    private $employeeService;
    private $leaveTypeService;
    private $companyService;
    private $leaveSummaryService;
    private $leaveEntitlementService;
    private $companyStructureService;
    private $jobTitleService;

    protected $locationChoices = null;
    protected $jobTitleChoices = null;
    protected $subDivisionChoices = null;
    protected $leaveTypeChoices = null;

    public $leaveSummaryEditMode = false;
    public $pageNo = 1;
    public $pager;
    public $offset = 0;
    public $recordsCount;
    public $recordsLimit = 20;
    public $saveSuccess;
    public $userType;
    public $loggedUserId;
    public $subordinatesList;
    public $currentLeavePeriodId;

    public function getJobTitleService() {
        if (!($this->jobTitleService instanceof JobTitleService)) {
            $this->jobTitleService = new JobTitleService();
        }
        return $this->jobTitleService;
    }

    public function configure() {

        $this->userType = $this->getOption('userType');
        $this->loggedUserId = $this->getOption('loggedUserId');
        $this->searchParam['employeeId'] = $this->getOption('employeeId');
        $this->searchParam['cmbWithTerminated'] = $this->getOption('cmbWithTerminated');
        $this->empId = $this->getOption('empId');

        $this->_setCurrentLeavePeriodId(); // This should be called before _setLeavePeriodWidgets()

        $formWidgets = array();
        $formValidators = array();

        if ($this->userType == 'Admin' || $this->userType == 'Supervisor') {

            $employeeId = 0;
            $empName = "";
            if (!is_null($this->searchParam['employeeId'])) {
                $employeeId = $this->searchParam['employeeId'];
                $employeeService = $this->getEmployeeService();
                $employee = $employeeService->getEmployee($this->searchParam['employeeId']);
                $empName = $employee->getFirstName() . " " . $employee->getMiddleName() . " " . $employee->getLastName();
            }

            /* Setting default values */
            $this->setDefault('txtEmpName', $empName);
            $this->setDefault('cmbEmpId', $employeeId);
            $this->setDefault('cmbLeavePeriod', $this->currentLeavePeriodId);
            $this->setDefault('hdnSubjectedLeavePeriod', $this->_getLeavePeriod());

            if ($this->searchParam['cmbWithTerminated'] == 'on') {
                $this->setDefault('cmbWithTerminated', true);
            }
        }

        $this->setWidgets($this->getFormWidgets());
        $this->setValidators($this->formValidators);

        $this->getWidgetSchema()->setNameFormat('leaveSummary[%s]');
        $this->getWidgetSchema()->setLabels($this->getFormLabels());

        $this->getWidgetSchema()->setFormFormatterName('BreakTags');
    }

    public function setEmployeeService(EmployeeService $employeeService) {
        $this->employeeService = $employeeService;
    }

    public function getEmployeeService() {
        if (is_null($this->employeeService)) {
            $this->employeeService = new EmployeeService();
            $this->employeeService->setEmployeeDao(new EmployeeDao());
        }
        return $this->employeeService;
    }

    public function setRecordsLimitDefaultValue() {
        $this->setDefault('cmbRecordsCount', $this->recordsLimit);
    }

    private function getLeavePeriodChoices() {

        if (is_null($this->leavePeriodChoices)) {
            $leavePeriodList = $this->getLeavePeriodService()->getLeavePeriodList();

            $this->leavePeriodChoices = array();

            sfContext::getInstance()->getConfiguration()->loadHelpers('OrangeDate');

            foreach ($leavePeriodList as $leavePeriod) {
                $this->leavePeriodChoices[$leavePeriod->getLeavePeriodId()] = set_datepicker_date_format($leavePeriod->getStartDate())
                        . ' ' . __('to') . ' '
                        . set_datepicker_date_format($leavePeriod->getEndDate());
            }

            if (empty($this->leavePeriodChoices)) {
                $this->leavePeriodChoices = array('0' => 'No Leave Periods');
            }
        }

        return $this->leavePeriodChoices;
    }

    public function setLeaveTypeService(LeaveTypeService $leaveTypeService) {
        $this->leaveTypeService = $leaveTypeService;
    }

    public function getLeaveTypeService() {
        if (!($this->leaveTypeService instanceof LeaveTypeService)) {
            $this->leaveTypeService = new LeaveTypeService();
        }
        return $this->leaveTypeService;
    }

    /**
     * 
     * @return array
     */
    private function getLeaveTypeChoices() {
        if (!($this->leaveTypeChoices)) {
            $leaveTypeList = $this->getLeaveTypeService()->getLeaveTypeList();

            $this->leaveTypeChoices = array('0' => __('All'));

            foreach ($leaveTypeList as $leaveType) {
                $this->leaveTypeChoices[$leaveType->getLeaveTypeId()] = $leaveType->getLeaveTypeName();
            }
        }

        return $this->leaveTypeChoices;
    }

    /**
     *
     * @return array
     */
    private function getRecordsPerPageChoices() {
        return array('20' => 20, '50' => 50, '100' => 100, '200' => 200);
    }

    public function getLocationService() {
        if (!($this->locationService instanceof LocationService)) {
            $this->locationService = new LocationService();
        }
        return $this->locationService;
    }

    public function setCompanyService(CompanyService $companyService) {
        $this->companyService = $companyService;
    }

    protected function getLocationChoices() {

        if (is_null($this->locationChoices)) {
            $locationList = $this->getLocationService()->getLocationList();

            $this->locationChoices = array('0' => __('All'));

            foreach ($locationList as $location) {
                $this->locationChoices[$location->getId()] = $location->getName();
            }
        }

        return $this->locationChoices;
    }

    public function getCompanyStructureService() {
        if (is_null($this->companyStructureService)) {
            $this->companyStructureService = new CompanyStructureService();
            $this->companyStructureService->setCompanyStructureDao(new CompanyStructureDao());
        }
        return $this->companyStructureService;
    }

    public function setCompanyStructureService(CompanyStructureService $companyStructureService) {
        $this->companyStructureService = $companyStructureService;
    }

    /**
     * 
     * @return array
     */
    private function getSubDivisionChoices() {

        if (is_null($this->subDivisionChoices)) {
            $this->subDivisionChoices = array(0 => __('All'));

            $treeObject = $this->getCompanyStructureService()->getSubunitTreeObject();

            $tree = $treeObject->fetchTree();

            foreach ($tree as $node) {
                if ($node->getId() != 1) {
                    $this->subDivisionChoices[$node->getId()] = str_repeat('&nbsp;&nbsp;', $node['level'] - 1) . $node['name'];
                }
            }
        }

        return $this->subDivisionChoices;
    }

    /**
     *
     * @return array
     */
    private function getJobTitleChoices() {

        if (is_null($this->jobTitleChoices)) {
            $jobTitleList = $this->getJobTitleService()->getJobTitleList();

            $this->jobTitleChoices = array('0' => __('All'));

            foreach ($jobTitleList as $jobTitle) {
                $this->jobTitleChoices[$jobTitle->getId()] = $jobTitle->getJobTitleName();
            }
        }

        return $this->jobTitleChoices;
    }

    /**
     * Is leave type editable? 
     * Always returns true in core module. Can be overridden to
     * support none editable leave types
     */
    protected function isLeaveTypeEditable($leaveTypeId) {
        return true;
    }

    public function getEmployeeListAsJson() {

        $jsonArray = array();
        $employeeService = $this->getEmployeeService();

        if ($this->userType == 'Admin') {
            $employeeList = $employeeService->getEmployeeList();
        } elseif ($this->userType == 'Supervisor') {

            $employeeList = $employeeService->getSupervisorEmployeeChain($this->loggedUserId);
            $loggedInEmployee = $employeeService->getEmployee($this->loggedUserId);
            array_push($employeeList, $loggedInEmployee);
        } else {

            $employeeList = array();
        }
        $employeeUnique = array();
        foreach ($employeeList as $employee) {
            if (!isset($employeeUnique[$employee->getEmpNumber()])) {
                $name = $employee->getFullName();

                $employeeUnique[$employee->getEmpNumber()] = $name;
                $jsonArray[] = array('name' => $name, 'id' => $employee->getEmpNumber());
            }
        }

        $jsonString = json_encode($jsonArray);

        return $jsonString;
    }

    public function setPager(sfWebRequest $request) {

        if ($request->isMethod('post')) {

            if ($request->getParameter('hdnAction') == 'search') {
                $this->pageNo = 1;
            } elseif ($request->getParameter('pageNo')) {
                $this->pageNo = $request->getParameter('pageNo');
            }
        } else {
            $this->pageNo = 1;
        }


        $this->pager = new SimplePager('LeaveSummary', $this->recordsLimit);
        $this->pager->setPage($this->pageNo);
        $this->pager->setNumResults($this->recordsCount);
        $this->pager->init();
        $offset = $this->pager->getOffset();
        $offset = empty($offset) ? 0 : $offset;
        $this->offset = $offset;
    }

    public function getLeaveSummaryRecordsCount() {

        $leaveSummaryService = $this->getLeaveSummaryService();
        $recordsCount = $leaveSummaryService->fetchRawLeaveSummaryRecordsCount($this->getSearchClues());

        return $recordsCount;
    }

    public function getLeaveSummaryService() {
        if (is_null($this->leaveSummaryService)) {
            $this->leaveSummaryService = new LeaveSummaryService();
            $this->leaveSummaryService->setLeaveSummaryDao(new LeaveSummaryDao());
        }
        return $this->leaveSummaryService;
    }

    public function setLeaveSummaryService(LeaveSummaryService $leaveSummaryService) {
        $this->leaveSummaryService = $leaveSummaryService;
    }

    public function setLeaveEntitlementService(LeaveEntitlementService $leaveEntitlementService) {
        $this->leaveEntitlementService = $leaveEntitlementService;
    }

    public function getLeaveEntitlementService() {
        if (is_null($this->leaveEntitlementService)) {
            $this->leaveEntitlementService = new LeaveEntitlementService();
            $this->leaveEntitlementService->setLeaveEntitlementDao(new LeaveEntitlementDao());
        }
        return $this->leaveEntitlementService;
    }

    public function saveEntitlements($request) {

        $hdnEmpId = $request->getParameter('hdnEmpId');
        $hdnLeaveTypeId = $request->getParameter('hdnLeaveTypeId');
        $hdnLeavePeriodId = $request->getParameter('hdnLeavePeriodId');
        $txtLeaveEntitled = $request->getParameter('txtLeaveEntitled');
        $count = count($txtLeaveEntitled);

        $leaveEntitlementService = $this->getLeaveEntitlementService();
        $leaveSummaryData = $request->getParameter('leaveSummary');

        for ($i = 0; $i < $count; $i++) {

            $leavePeriodId = empty($hdnLeavePeriodId[$i]) ? $leaveSummaryData['hdnSubjectedLeavePeriod'] : $hdnLeavePeriodId[$i];

            $leaveEntitlementService->saveEmployeeLeaveEntitlement($hdnEmpId[$i], $hdnLeaveTypeId[$i], $leavePeriodId, $txtLeaveEntitled[$i], true);
        }

        $this->saveSuccess = true;
    }

    public function getSearchClues() {

        if ($this->getValues()) {

            return $this->_adjustSearchClues($this->getValues());
        } else {

            $clues['cmbLeavePeriod'] = $this->currentLeavePeriodId;
            $clues['cmbEmpId'] = 0;
            if (!is_null($this->searchParam['employeeId'])) {
                $clues['cmbEmpId'] = $this->searchParam['employeeId'];
            }

            $clues['cmbLeaveType'] = 0;
            $clues['cmbLocation'] = 0;
            $clues['cmbSubDivision'] = 0;
            $clues['cmbJobTitle'] = 0;
            $clues['cmbWithTerminated'] = 0;

            return $this->_adjustSearchClues($clues);
        }
    }

    private function _adjustSearchClues($clues) {

        if ($this->userType == 'Admin') {

            $clues['userType'] = 'Admin';
            return $clues;
        } elseif ($this->userType == 'Supervisor') {

            $clues['userType'] = 'Supervisor';
            $clues['subordinates'] = $this->_getSubordinatesIds();
            return $clues;
        } else {

            $clues['userType'] = 'ESS';
            $clues['cmbEmpId'] = $this->loggedUserId;
            return $clues;
        }
    }

    private function _getSubordinatesList() {

        if (!empty($this->subordinatesList)) {

            return $this->subordinatesList;
        } else {

            $employeeService = new EmployeeService();
            $employeeService->setEmployeeDao(new EmployeeDao());
            $this->subordinatesList = $employeeService->getSupervisorEmployeeChain($this->loggedUserId, true);

            return $this->subordinatesList;
        }
    }

    private function _getSubordinatesIds() {

        $ids = array();

        foreach ($this->_getSubordinatesList() as $employee) {

            $ids[] = $employee->getEmpNumber();
        }

        $ids[] = $this->loggedUserId;

        return $ids;
    }

    private function _getLeavePeriod() {

        if ($this->getValue('cmbLeavePeriod')) {

            return $this->getValue('cmbLeavePeriod');
        } else {

            return $this->currentLeavePeriodId;
        }
    }

    private function _setCurrentLeavePeriodId() {

        $leavePeriodService = $this->getLeavePeriodService();
        $this->currentLeavePeriodId = (!$leavePeriodService->getCurrentLeavePeriod() instanceof LeavePeriod) ? 0 : $leavePeriodService->getCurrentLeavePeriod()->getLeavePeriodId();
    }

    /**
     * Returns LeavePeriodService
     * @return LeavePeriodService
     */
    public function getLeavePeriodService() {
        if (is_null($this->leavePeriodService)) {
            $this->leavePeriodService = new LeavePeriodService();
        }
        return $this->leavePeriodService;
    }

    /**
     * Sets LeavePeriodService
     * @param LeavePeriodService $leavePeriodService
     */
    public function setLeavePeriodService(LeavePeriodService $leavePeriodService) {
        $this->leavePeriodService = $leavePeriodService;
    }

    /**
     *
     * @return array
     */
    protected function getFormLabels() {
        $labels = array(
            'cmbLeavePeriod' => __('Leave Period'),
            'cmbLeaveType' => __('Leave Type'),
            'cmbRecordsCount' => __('Records Per Page'),
            'cmbLocation' => __('Location'),
            'cmbJobTitle' => __('Job Title'),
            'cmbSubDivision' => __('Sub Unit'),
            'cmbWithTerminated' => __('Include Past Employees'),
            'txtEmpName' => __('Employee'),
        );

        return $labels;
    }

    /**
     *
     * @return array 
     */
    protected function getFormWidgets() {
        $widgets = array();


        $widgets['cmbLeavePeriod'] = new sfWidgetFormChoice(array('choices' => $this->getLeavePeriodChoices()));
        $widgets['cmbLeaveType'] = new sfWidgetFormChoice(array('choices' => $this->getLeaveTypeChoices()));
        $widgets['cmbRecordsCount'] = new sfWidgetFormChoice(array('choices' => $this->getRecordsPerPageChoices()));


        $widgets['cmbLocation'] = new sfWidgetFormChoice(array('choices' => $this->getLocationChoices()));
        $widgets['cmbJobTitle'] = new sfWidgetFormChoice(array('choices' => $this->getJobTitleChoices()));
        $widgets['cmbSubDivision'] = new sfWidgetFormChoice(array('choices' => $this->getSubDivisionChoices()));
        $widgets['cmbWithTerminated'] = new sfWidgetFormInputCheckbox(array('value_attribute_value' => 'on'));
        $widgets['txtEmpName'] = new sfWidgetFormInput(array(), array('class' => ''));
        $widgets['cmbEmpId'] = new sfWidgetFormInputHidden();
        $widgets['hdnSubjectedLeavePeriod'] = new sfWidgetFormInputHidden();

        return $widgets;
    }

    /**
     *
     * @return array 
     */
    protected function getFormValidators() {
        $validators = array();

        $validators['cmbLeavePeriod'] = new sfValidatorChoice(array('choices' => array_keys($this->getLeavePeriodChoices())));
        $validators['cmbLeaveType'] = new sfValidatorChoice(array('choices' => array_keys($this->getLeaveTypeChoices())));
        $validators['cmbRecordsCount'] = new sfValidatorChoice(array('choices' => array_keys($this->getRecordsPerPageChoices())));

        $validators['cmbLocation'] = new sfValidatorChoice(array('choices' => array_keys($this->getLocationChoices())));
        ;
        $validators['cmbJobTitle'] = new sfValidatorChoice(array('choices' => array_keys($this->getJobTitleChoices())));
        $validators['cmbSubDivision'] = new sfValidatorChoice(array('choices' => array_keys($this->getSubDivisionChoices())));
        $validators['cmbWithTerminated'] = new sfValidatorString(array('required' => false));
        $validators['txtEmpName'] = new sfValidatorString(array('required' => false));
        $validators['cmbEmpId'] = new sfValidatorString(array('required' => false));
        $validators['hdnSubjectedLeavePeriod'] = new sfValidatorString(array('required' => false));

        return $validators;
    }

}
