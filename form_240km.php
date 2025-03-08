<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borang Permohonan Untuk Menggunakan Kenderaan Sendiri Dan Menuntut Elaun Kilometer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 11px;
            max-width: 21cm; /* A4 width */
            margin: 0 auto;
        }
        .container {
            padding: 10px 20px;
        }
        .header {
            display: flex;
            position: relative;
        }
        .form-number {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 10px;
        }
        .logo {
            width: 55px;
            height: auto;
            margin-right: 10px;
            margin-top: 5px;
        }
        .header-text {
            font-size: 11px;
            font-weight: bold;
            line-height: 1.3;
        }
        .form-title {
            text-align: center;
            font-weight: bold;
            margin: 15px 0 5px 0;
            font-size: 12px;
        }
        .form-subtitle {
            text-align: center;
            font-size: 11px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        td {
            padding: 5px;
            font-size: 11px;
            vertical-align: top;
        }
        input[type="text"] {
            width: 100%;
            border: none;
            background: transparent;
            outline: none;
            font-size: 11px;
            box-sizing: border-box;
        }
        .label-field {
            display: flex;
            margin: 5px 0;
        }
        .label-field label {
            width: 120px;
            font-weight: normal;
        }
        .label-field .field {
            flex: 1;
            border-bottom: 1px solid black;
            padding-left: 5px;
        }
        .reasons-section {
            margin: 15px 0;
        }
        .reason-item {
            display: flex;
            align-items: flex-start;
            margin: 8px 0;
        }
        .checkbox-container {
            width: 20px;
            height: 16px;
            border: 1px solid black;
            margin-right: 10px;
            margin-top: 1px;
        }
        .reason-text {
            flex: 1;
        }
        .approval-section {
            margin-top: 20px;
            border-top: 1px solid black;
            padding-top: 10px;
        }
        .approval-title {
            text-align: center;
            font-weight: bold;
            margin: 10px 0;
        }
        .signature-section {
            margin: 20px 0;
        }
        .signature-line {
            border-bottom: 1px solid black;
            width: 200px;
            display: inline-block;
            margin: 0 10px;
        }
        .date-field {
            margin: 10px 0;
        }
        .date-field .signature-line {
            width: 150px;
        }
        .table-header {
            font-weight: bold;
            text-align: center;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="form-number">IPGKBM.UKP/BPGK240.v1</div>
            <img class="logo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/Jata_MalaysiaV2.svg/2558px-Jata_MalaysiaV2.svg.png" alt="Jata Malaysia">
            <div class="header-text">
                INSTITUT PENDIDIKAN GURU<br>
                KAMPUS BAHASA MELAYU<br>
                Jalan Pantai Baru<br>
                59990 KUALA LUMPUR
            </div>
        </div>

        <div class="form-title">BORANG PERMOHONAN UNTUK MENGGUNAKAN KENDERAAN SENDIRI DAN MENUNTUT ELAUN</div>
        <div class="form-title">KILOMETER BAGI JARAK MELEBIHI 240KM SEHALA</div>
        <div class="form-subtitle">(PEKELILING PERBENDAHARAAN BIL.2/1992 – PARA 4.7.3)</div>

        <div class="personal-info">
            <div class="label-field">
                <label>Nama Pegawai</label>
                <div class="field"><input type="text" name="nama_pegawai"></div>
            </div>
            <div class="label-field">
                <label>Jawatan</label>
                <div class="field"><input type="text" name="jawatan"></div>
            </div>
            <div class="label-field">
                <label>Jabatan / Unit</label>
                <div class="field"><input type="text" name="jabatan_unit"></div>
            </div>
        </div>

        <p>Saya dengan ini memohon untuk menggunakan kenderaan sendiri bagi menjalankan tugas rasmi di luar pejabat seperti berikut:</p>

        <table>
            <tr>
                <td class="table-header" style="width:15%">Tarikh</td>
                <td class="table-header" style="width:35%">Tempat Bertugas Rasmi</td>
                <td class="table-header" style="width:25%">Jenis Tugas</td>
                <td class="table-header" style="width:25%">Anggaran Jarak Pergi/Balik</td>
            </tr>
            <tr>
                <td style="height:80px"></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <div class="reasons-section">
            <p>Sebab-sebab membuat perjalanan dengan menggunakan kenderaan sendiri:</p>
            
            <div class="reason-item">
                <div class="checkbox-container"></div>
                <div class="reason-text">Perlu menjalankan tugas rasmi di beberapa tempat disepanjang perjalanan</div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox-container"></div>
                <div class="reason-text">Mustahak dan terpaksa berkenderaan sendiri kerana <input type="text" style="width:300px;border-bottom:1px solid black"></div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox-container"></div>
                <div class="reason-text">
                    Mustahak dan terpaksa membawa pegawai lain sebagai penumpang yang juga menjalankan 
                    tugas rasmi. Nama pegawai yang dibawa:<br>
                    a) <input type="text" style="width:300px;border-bottom:1px solid black"><br>
                    b) <input type="text" style="width:300px;border-bottom:1px solid black">
                </div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox-container"></div>
                <div class="reason-text">Menggunakan kenderaan sendiri dengan menuntut tambang gantian persamaan dengan tambang kapal terbang</div>
            </div>
        </div>

        <div class="signature-section">
            <div style="display:flex; justify-content:space-between;">
                <div class="date-field">
                    Tarikh: <span class="signature-line"></span>
                </div>
                <div>
                    Tandatangan Pemohon: <span class="signature-line"></span>
                </div>
            </div>
        </div>

        <div class="approval-section">
            <div class="approval-title">KELULUSAN PENGARAH</div>
            
            <div class="reason-item">
                <div class="checkbox-container"></div>
                <div class="reason-text">Diluluskan termaktub kepada syarat-syarat di dalam Pekeliling Perbendaharaan Bil. 3 Tahun 2003</div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox-container"></div>
                <div class="reason-text">Diluluskan dengan menuntut tambang gantian</div>
            </div>
            
            <div class="reason-item">
                <div class="checkbox-container"></div>
                <div class="reason-text">Tidak diluluskan</div>
            </div>
            
            <div class="signature-section">
                <div class="label-field">
                    <label>Tandatangan</label>
                    <div class="field"></div>
                </div>
                
                <div class="label-field">
                    <label>Cop Nama dan Jawatan</label>
                    <div class="field"></div>
                </div>
                
                <div class="label-field">
                    <label>Tarikh</label>
                    <div class="field"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 