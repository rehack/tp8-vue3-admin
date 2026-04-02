<?php
namespace app\api\service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PhpOffice{
    /**
    * @title 导出表格
    * @param fileName：excel表的表名
    * @param data：要导出excel表的数据，接受一个二维数组
    * @param headArr：excel表的表头，接受一个一维数组
    */

    public function exportExcel($fileName='表格',$data=[],$headArr=[]){
        // 创建一个新的工作簿实例
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // 设置表头
        // $sheet->setCellValue('A1','ID');
        // $sheet->setCellValue('B1','姓名');
        // $sheet->setCellValue('C1','年龄');
        // $sheet->setCellValue('D1','身高');
        foreach ($headArr as $key => $value) {
            $sheet->setCellValue($key,$value);
            $sheet->getStyle($key)->getFont()->setBold(true);
            $sheet->getStyle($key)->getFont()->getColor()->setARGB('FFFF0000');
            
        }
        // 设置单元格数据
        // $sheet->setCellValueByColumnAndRow(1, 2, 1);
        // $sheet->setCellValueByColumnAndRow(2, 2, '欧阳克');
        // $sheet->setCellValueByColumnAndRow(3, 2, '18岁');
        // $sheet->setCellValueByColumnAndRow(4, 2, '188cm');

        /**
         * 批量赋值
         * fromArray 从数组中的值填充工作表
         * 参数1：数据(数组)
         * 参数2：去除某个值
         * 参数3：从哪个位置开始    
         */
        
        $sheet->fromArray(
            $data,
            NULL,
            'A2'
        );


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //MIME 协议，文件的类型，不设置，会默认html
        header("Content-Disposition: attachment;filename=$fileName.xlsx"); //MIME 协议的扩展
        header('Cache-Control: max-age=0'); // 缓存控制
        $objWriter = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        
        // 从内存中清除工作簿
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        exit();
        
    }


    //导入
    public function importExcel(){
        set_time_limit(0);  
        //文件上传导入
        // $fileController=new FileController();
        $res=self::uploadFileImport();
        $data=$res['data'];
        //手动输入路径
        // $data='muban/muban.xlsx';

        //修正路径
        $filename=str_replace('/uploads', 'uploads',$data);
        //进行读取
        $spreadsheet = IOFactory::load($filename);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        return $sheetData;
    }
}