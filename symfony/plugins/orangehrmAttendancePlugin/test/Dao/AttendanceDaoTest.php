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

namespace OrangeHRM\Tests\Attendance\Dao;

use DateTime;
use Exception;
use OrangeHRM\Admin\Service\CompanyStructureService;
use OrangeHRM\Attendance\Dao\AttendanceDao;
use OrangeHRM\Attendance\Exception\AttendanceServiceException;
use OrangeHRM\Config\Config;
use OrangeHRM\Framework\Services;
use OrangeHRM\Tests\Util\KernelTestCase;
use OrangeHRM\Tests\Util\TestDataService;
use OrangeHRM\Time\Dto\AttendanceReportSearchFilterParams;

/**
 * @group Attendance
 * @group Dao
 */
class AttendanceDaoTest extends KernelTestCase
{
    /**
     * @var AttendanceDao
     */
    private AttendanceDao $attendanceDao;

    /**
     * @var string
     */
    protected string $fixtures;

    /**
     * Set up method
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->attendanceDao = new AttendanceDao();
        $this->fixture = Config::get(Config::PLUGINS_DIR) . '/orangehrmAttendancePlugin/test/fixtures/AttendanceDaoTest.yaml';
        TestDataService::populate($this->fixture);
    }

    public function testGetLatestAttendanceRecordByEmployeeId(): void
    {
        $attendanceRecord = $this->attendanceDao->getLatestAttendanceRecordByEmployeeNumber(1);
        $this->assertEquals(1, $attendanceRecord->getEmployee()->getEmpNumber());
        $this->assertEquals('Kayla', $attendanceRecord->getEmployee()->getFirstName());
        $this->assertEquals('Abbey', $attendanceRecord->getEmployee()->getLastName());
        $this->assertEquals('2011-05-27', $attendanceRecord->getPunchInUserTime()->format('Y-m-d'));
        $this->assertEquals('2011-05-27', $attendanceRecord->getPunchInUserTime()->format('Y-m-d'));
        $this->assertEquals('2011-05-28', $attendanceRecord->getPunchOutUtcTime()->format('Y-m-d'));
        $this->assertEquals('2011-05-28', $attendanceRecord->getPunchOutUserTime()->format('Y-m-d'));
        $this->assertEquals('PUNCHED OUT', $attendanceRecord->getState());
    }

    public function testPunchInOverlapRecords(): void
    {
        try {
            $this->attendanceDao->checkForPunchInOverLappingRecords(new DateTime("2022-01-27 09:23:00"), 4);
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof AttendanceServiceException);
            $this->assertEquals("Cannot Proceed Punch In Employee Already Punched In", $exception->getMessage());
        }

        $overlapStatus = $this->attendanceDao->checkForPunchInOverLappingRecords(
            new DateTime("2011-04-22 09:25:00"),
            5
        );
        $this->assertFalse($overlapStatus);

        $overlapStatus = $this->attendanceDao->checkForPunchInOverLappingRecords(
            new DateTime("2011-04-21 09:26:00"),
            5
        );
        $this->assertTrue($overlapStatus);
    }

    public function testPunchOutOverlapRecords(): void
    {
        try {
            $this->attendanceDao->checkForPunchOutOverLappingRecords(
                new DateTime("2022-01-27 09:23:00"),
                1
            );
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof AttendanceServiceException);
            $this->assertEquals("Cannot Proceed Punch Out Employee Already Punched Out", $exception->getMessage());
        }

        try {
            $this->attendanceDao->checkForPunchOutOverLappingRecords(
                new DateTime("2022-01-27 09:20:00"),
                4
            );
        } catch (Exception $exception) {
            $this->assertTrue($exception instanceof AttendanceServiceException);
            $this->assertEquals("Punch Out Time Should Be Later Than Punch In Time", $exception->getMessage());
        }

        $overlapStatus = $this->attendanceDao->checkForPunchOutOverLappingRecords(
            new DateTime("2011-04-21 09:26:00"),
            2
        );
        $this->assertFalse($overlapStatus);

        $overlapStatus = $this->attendanceDao->checkForPunchOutOverLappingRecords(
            new DateTime("2011-04-20 09:29:00"),
            2
        );
        $this->assertTrue($overlapStatus);
    }

    public function testAttendanceSummeryReport(): void
    {
        $this->fixture = Config::get(Config::PLUGINS_DIR) . '/orangehrmTimePlugin/test/fixtures/AttendanceReportDataAPITest.yaml';
        TestDataService::populate($this->fixture);
        $attendanceReportSearchFilterParams = new AttendanceReportSearchFilterParams();
        $result = $this->attendanceDao->getAttendanceReportCriteriaList($attendanceReportSearchFilterParams);
        $totalRecords = $this->attendanceDao->getAttendanceReportCriteriaListCount($attendanceReportSearchFilterParams);
        $this->assertEquals("Kayla Abbey", $result[0]['fullName']);
        $this->assertEquals("64800", $result[0]['total']);
        $this->assertEquals(1, $result[0]['empNumber']);
        $this->assertEquals(10, $totalRecords);

        $attendanceReportSearchFilterParams = new AttendanceReportSearchFilterParams();
        $attendanceReportSearchFilterParams->setFromDate(new DateTime("2011-01-01"));
        $result = $this->attendanceDao->getAttendanceReportCriteriaList($attendanceReportSearchFilterParams);
        $totalRecords = $this->attendanceDao->getAttendanceReportCriteriaListCount($attendanceReportSearchFilterParams);

        $this->assertEquals("Ashley Abel", $result[1]['fullName']);
        $this->assertEquals("32400", $result[1]['total']);
        $this->assertEquals(10, $totalRecords);

        $attendanceReportSearchFilterParams = new AttendanceReportSearchFilterParams();
        $attendanceReportSearchFilterParams->setToDate(new DateTime("2011-12-31"));
        $result = $this->attendanceDao->getAttendanceReportCriteriaList($attendanceReportSearchFilterParams);
        $totalRecords = $this->attendanceDao->getAttendanceReportCriteriaListCount($attendanceReportSearchFilterParams);
        $this->assertEquals("mahatma gandhi", $result[2]['fullName']);
        $this->assertEquals("86460", $result[2]['total']);
        $this->assertEquals(10, $totalRecords);

        $attendanceReportSearchFilterParams = new AttendanceReportSearchFilterParams();
        $attendanceReportSearchFilterParams->setFromDate(new DateTime("2011-01-01"));
        $attendanceReportSearchFilterParams->setToDate(new DateTime("2021-01-31"));
        $result = $this->attendanceDao->getAttendanceReportCriteriaList($attendanceReportSearchFilterParams);
        $this->assertCount(10, $result);

        $attendanceReportSearchFilterParams = new AttendanceReportSearchFilterParams();
        $this->createKernelWithMockServices(
            [
                Services::COMPANY_STRUCTURE_SERVICE => new CompanyStructureService(),
            ]
        );
        $attendanceReportSearchFilterParams->setFromDate(new DateTime("2011-01-01"));
        $attendanceReportSearchFilterParams->setToDate(new DateTime("2021-12-31"));
        $attendanceReportSearchFilterParams->setJobTitleId(1);
        $attendanceReportSearchFilterParams->setEmploymentStatusId(1);
        $attendanceReportSearchFilterParams->setSubUnitId(2);
        $result = $this->attendanceDao->getAttendanceReportCriteriaList($attendanceReportSearchFilterParams);
        $totalRecords = $this->attendanceDao->getAttendanceReportCriteriaListCount($attendanceReportSearchFilterParams);

        $this->assertEquals("Kayla Abbey", $result[0]['fullName']);
        $this->assertEquals(1, $result[0]['empNumber']);
        $this->assertNull($result[0]['termination']);
        $this->assertEquals("Adolf Hitler", $result[1]['fullName']);
        $this->assertEquals(5, $result[1]['empNumber']);
        $this->assertNull($result[1]['termination']);
        $this->assertEquals(2, $totalRecords);

        $attendanceReportSearchFilterParams->setFromDate(new DateTime("2011-01-01"));
        $attendanceReportSearchFilterParams->setToDate(new DateTime("2021-12-31"));
        $attendanceReportSearchFilterParams->setEmployeeNumbers([1]);
        $attendanceReportSearchFilterParams->setJobTitleId(1);
        $attendanceReportSearchFilterParams->setEmploymentStatusId(1);
        $attendanceReportSearchFilterParams->setSubUnitId(2);
        $result = $this->attendanceDao->getAttendanceReportCriteriaList($attendanceReportSearchFilterParams);
        $totalRecords = $this->attendanceDao->getAttendanceReportCriteriaListCount($attendanceReportSearchFilterParams);

        $this->assertEquals("Kayla Abbey", $result[0]['fullName']);
        $this->assertEquals(1, $result[0]['empNumber']);
        $this->assertNull($result[0]['termination']);
        $this->assertEquals(1, $totalRecords);
    }
}