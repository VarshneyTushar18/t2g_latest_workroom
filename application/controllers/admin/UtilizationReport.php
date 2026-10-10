<?php

defined('BASEPATH') or exit('No direct script access allowed');

class UtilizationReport extends AdminController
{

    public function __construct()
    {
        parent::__construct();
    //    if (!has_permission('reports', '', 'view')) {
     //       access_denied('reports');
      //  }
       // $this->ci = &get_instance();
        $this->load->model('utilization_model');
    }

    /* No access on this url */
    public function index()
    {
		$data['result'] = $this->utilization_model->getDepartment();
		//echo "<pre>"; print_r($data);die;
		$this->load->view('admin/utilization_report/manage', $data);
    }
	public function getDeptemp()
    {	
		$deptid = $_POST['department'];
	
		$data['emp'] = $this->utilization_model->getDepartmentemp($deptid);
	
		echo json_encode($data['emp']);
    }
	public function getFilterData()
	{
		$deptid = $_POST['department'];
		$empid = $_POST['employee'];
		$sdate = $_POST['start_date'];
		$edate = $_POST['end_date'];
		$data['emp'] = $this->utilization_model->getFilterData($deptid,$empid,$sdate,$edate);
		echo json_encode($data['emp']);
	}
	

}