<?php 

// create session
session_start();

if(isset($_SESSION['username']) && isset($_SESSION['level']))
{
  // include file
  include('../layouts/header.php');
  include('../layouts/topbar.php');
  include('../layouts/sidebar.php');

  // active nhan vien
  if(isset($_GET['id']))
  {
    $id = $_GET['id'];
    $act = "SELECT ma_cong_tac, nv.id as idNhanVien, ma_nv, ten_nv, ngay_bat_dau, ngay_ket_thuc, dia_diem, muc_dich, ghi_chu FROM cong_tac ct, nhanvien nv WHERE ct.nhanvien_id = nv.id AND ct.id = $id";
    $resultAct = mysqli_query($conn, $act);
    $rowAct = mysqli_fetch_array($resultAct);
    $idNhanVien = $rowAct['idNhanVien'];
  }

  // create code room
  $maCongTac = "MCT" . time();

  // delete record
  if(isset($_POST['save']))
  {
    // create array error
    $error = array();
    $success = array();
    $showMess = false;

    // get id in form
    $maNhanVien = $_POST['maNhanVien'];
    $ngayBatDau = $_POST['ngayBatDau'];
    $ngayKetThuc = $_POST['ngayKetThuc'];
    $diaDiem = $_POST['diaDiem'];
    $mucDich = $_POST['mucDich'];
    $ghiChu = $_POST['ghiChu'];
    $nguoiTao = $_POST['nguoiTao'];
    $ngayTao = date("Y-m-d H:i:s");

    // validate
    if($maNhanVien == 'chon')
      $error['maNhanVien'] = 'error';
    if(empty($ngayKetThuc))
      $error['ngayKetThuc'] = 'error';
    if(!empty($ngayKetThuc) && ($ngayBatDau > $ngayKetThuc))
      $error['loiNgay'] = 'error';
    if(empty($diaDiem))
      $error['diaDiem'] = 'error';


    if(!$error)
    {
      $showMess = true;
      $update = " UPDATE cong_tac SET
                  ngay_bat_dau = '$ngayBatDau',
                  ngay_ket_thuc = '$ngayKetThuc',
                  dia_diem = '$diaDiem',
                  muc_dich = '$mucDich',
                  ghi_chu = '$ghi_chu',
                  nguoi_sua = '$nguoiTao',
                  ngay_sua = '$ngayTao'
                  WHERE id = $id";

      $result = mysqli_query($conn, $update);
      if($result)
      {
        $success['success'] = 'L??u l???i th??nh c??ng';
        echo '<script>setTimeout("window.location=\'sua-cong-tac.php?p=collaborate&a=add-collaborate&id='.$id.'\'",1000);</script>';
      }
    }
  }

?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        C??ng t??c
      </h1>
      <ol class="breadcrumb">
        <li><a href="index.php?p=index&a=statistic"><i class="fa fa-dashboard"></i> T???ng quan</a></li>
        <li><a href="cong-tac.php?p=collaborate&a=add-collaborate">C??ng t??c</a></li>
        <li class="active">Th??m c??ng t??c</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Th??m c??ng t??c</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
              </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              <?php 
                // show error
                if($row_acc['quyen'] != 1) 
                {
                  echo "<div class='alert alert-warning alert-dismissible'>";
                  echo "<h4><i class='icon fa fa-ban'></i> Th??ng b??o!</h4>";
                  echo "B???n <b> kh??ng c?? quy???n </b> th???c hi???n ch???c n??ng n??y.";
                  echo "</div>";
                }
              ?>
              <?php 
                // show success
                if(isset($success)) 
                {
                  if($showMess == true)
                  {
                    echo "<div class='alert alert-success alert-dismissible'>";
                    echo "<h4><i class='icon fa fa-check'></i> Th??nh c??ng!</h4>";
                    foreach ($success as $suc) 
                    {
                      echo $suc . "<br/>";
                    }
                    echo "</div>";
                  }
                }
              ?>
              <form action="" method="POST">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="exampleInputEmail1">M?? c??ng t??c: </label>
                      <input type="text" class="form-control" id="exampleInputEmail1" name="maCongTac" value="<?php echo $rowAct['ma_cong_tac']; ?>" readonly>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Nh??n vi??n: </label>
                      <input type="text" class="form-control" id="exampleInputEmail1" name="maNhanVien" value="<?php echo $rowAct['ma_nv']; ?> - <?php echo $rowAct['ten_nv']; ?>" readonly>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Ng??y b???t ?????u<span style="color: red;">*</span>: </label>
                      <input type="date" class="form-control" id="exampleInputEmail1" name="ngayBatDau" value="<?php echo date_format(date_create($rowAct['ngay_bat_dau']), 'Y-m-d'); ?>">
                      <small style="color: red;"><?php if(isset($error['loiNgay'])){ echo 'Ng??y b???t ?????u <b> kh??ng ???????c sau </b> ng??y k???t th??c';} ?></small>
                    </div>  
                    <div class="form-group">
                      <label for="exampleInputEmail1">Ng??y k???t th??c<span style="color: red;">*</span>: </label>
                      <input type="date" class="form-control" id="exampleInputEmail1" name="ngayKetThuc" value="<?php echo date_format(date_create($rowAct['ngay_ket_thuc']), 'Y-m-d'); ?>">
                      <small style="color: red;"><?php if(isset($error['ngayKetThuc'])){ echo 'Vui l??ng ch???n ng??y k???t th??c';} ?></small>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">?????a ??i???m c??ng t??c<span style="color: red;">*</span>: </label>
                      <input type="text" class="form-control" id="exampleInputEmail1" name="diaDiem" placeholder="Vui l??ng nh???p ?????a ??i???m" value="<?php echo $rowAct['dia_diem']; ?>">
                      <small style="color: red;"><?php if(isset($error['diaDiem'])){ echo 'Vui l??ng nh???p ?????a ??i???m c??ng t??c';} ?></small>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">M???c ????ch c??ng t??c: </label>
                      <textarea id="editor1" rows="10" cols="80" name="mucDich"><?php echo $rowAct['muc_dich']; ?>
                      </textarea>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Ghi ch??: </label>
                      <textarea id="editor" class="form-control" name="ghiChu"></textarea><?php echo $rowAct['ghi_chu']; ?>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Ng?????i t???o: </label>
                      <input type="text" class="form-control" id="exampleInputEmail1" value="<?php echo $row_acc['ho']; ?> <?php echo $row_acc['ten']; ?>" name="nguoiTao" readonly>
                    </div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">Ng??y t???o: </label>
                      <input type="text" class="form-control" value="<?php echo date('Y-m-d'); ?>" name="ngayTao" readonly>
                    </div>
                    <!-- /.form-group -->
                    <?php 
                      if($_SESSION['level'] == 1)
                        echo "<button type='submit' class='btn btn-warning' name='save'><i class='fa fa-save'></i> L??u l???i</button>";
                    ?>
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </form>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>

<?php
  // include
  include('../layouts/footer.php');
}
else
{
  // go to pages login
  header('Location: dang-nhap.php');
}

?>