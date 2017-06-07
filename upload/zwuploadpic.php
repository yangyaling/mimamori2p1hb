<?php
/**
 * Created by PhpStorm.
 * User: NITIOSRD
 * Date: 2016/12/30
 * Time: 15:25
 */
header('Content-type: text/json; charset=UTF-8');

include '../lib.php';

//$connectionOptions = array('Database' => DATABASE, 'Uid' => UID, 'PWD' => PWD, 'CharacterSet' => 'UTF-8');
//$conn = sqlsrv_connect(SERVERNAME, $connectionOptions);
//
$arrReturn = array();
$code = '200';
$errors = array();

$hostCd = $_POST['hostcd'];
$customerId = $_POST['custid'];
$updateDate = $_POST['updatedate'];
$picData = $_POST['picdata'];

$allowType = array("png", "gif", "jpg", "jpeg", "bmp", "PNG", "GIF", "JPG", "JPEG", "BMP");
$suffix = ".jpg";
$picPath = "";

function updatePicInfo($conn, $customerId, $picPath, $updateDate, &$code)
{
    $sql = "SELECT custid FROM AZW005_custmst WHERE custid='$customerId'";
    $result = sqlsrv_query($conn, $sql);

    if (sqlsrv_has_rows($result)) {
        $sql = "UPDATE AZW008_custrelation SET picpath='$picPath',picupdatedate='$updateDate' WHERE custid='$customerId'";
        $result = sqlsrv_query($conn, $sql);
        if (!$result) {
            $code = '503';
        }
    } else {
        $code = '502';
    }
}

if ($conn) {
    if (!is_empty($customerId) && !is_empty($updateDate)) {
        /*
        0002
        2016-12-30 16:59:16
        <ffd8ffe0 00104a46 49460001 01000048 00480000 ffe10058 45786966 00004d4d 002a0000 00080002 01120003 00000001 00010000 87690004 00000001 00000026 00000000 0003a001 00030000 00010001 0000a002 00040000 00010000 0280a003 00040000 00010000 02800000 0000ffed 00385068 6f746f73 686f7020 332e3000 3842494d 04040000 00000000 3842494d 04250000 00000010 d41d8cd9 8f00b204 e9800998 ecf8427e ffc00011 08028002 80030122 00021101 031101ff c4001f00 00010501 01010101 01000000 00000000 00010203 04050607 08090a0b ffc400b5 10000201 03030204 03050504 04000001 7d010203 00041105 12213141 06135161 07227114 328191a1 082342b1 c11552d1 f0243362 7282090a 16171819 1a252627 28292a34 35363738 393a4344 45464748 494a5354 55565758 595a6364 65666768 696a7374 75767778 797a8384 85868788 898a9293 94959697 98999aa2 a3a4a5a6 a7a8a9aa b2b3b4b5 b6b7b8b9 bac2c3c4 c5c6c7c8 c9cad2d3 d4d5d6d7 d8d9dae1 e2e3e4e5 e6e7e8e9 eaf1f2f3 f4f5f6f7 f8f9faff c4001f01 00030101 01010101 01010100 00000000 00010203 04050607 08090a0b ffc400b5 11000201 02040403 04070504 04000102 77000102 03110405 21310612 41510761 71132232 81081442 91a1b1c1 09233352 f0156272 d10a1624 34e125f1 1718191a 26272829 2a353637 38393a43 44454647 48494a53 54555657 58595a63 64656667 68696a73 74757677 78797a82 83848586 8788898a 92939495 96979899 9aa2a3a4 a5a6a7a8 a9aab2b3 b4b5b6b7 b8b9bac2 c3c4c5c6 c7c8c9ca d2d3d4d5 d6d7d8d9 dae2e3e4 e5e6e7e8 e9eaf2f3 f4f5f6f7 f8f9faff db004300 0f0f0f0f 0f0f1a0f 0f1a241a 1a1a2431 24242424 313e3131 3131313e 4b3e3e3e 3e3e3e4b 4b4b4b4b 4b4b4b5a 5a5a5a5a 5a696969 69697676 76767676 76767676 ffdb0043 01121313 1e1c1e34 1c1c347b 5445547b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7b7b7b7b 7bffdd00 040028ff da000c03 01000211 0311003f 00db962f 308f6a81 edf6dbc9 1e720838 cd5ca4dc 8d950413 48470b8e 68a7c8bb 64643d89 14c38a0a 1a7ad369 c69b400e 0d814aad b4e7ae41 07f1a651 40161629 a4c6c19e 07e5d2a4 6b49c025 88e066a1 49a45002 70718cfe b43cd348 30c6a5f3 5f41ea3c 5b4df772 300fafe7 4e168db8 2b3609cf 63daa1f3 66ceede7 3d699297 de779c9c f5a5690a ccb42da3 00177fe1 ddfe229e ab68ac0e e04018e7 d7d6b3a9 28e57dc0 b24c2272 7194f6fe 94ff003a 11fc1938 c74aa949 4ec4b8a7 b971ae54 8c0403a1 18f6a61b 97ce40ee 4f7ef55a 8a395072 22533393 938efdbd 698589e4 d368a652 d361c5dc f534dc9a 4a281dc5 a4a28cd3 10514525 0316928a 2810b452 51400b49 45140051 40049e39 a4a005a2 928a005a 2928a005 a4a4cd2d 200a2929 69806696 9b45002d 14945201 68a4a334 00b45251 4c05cd14 94520168 a28a0029 6928a005 a2928a06 3a8a4a28 01734525 2d002d6d e84d8ba6 1eabfd45 61d6c68a d8be1eea 450236f5 903c946f f6bfa571 7d18d76f ac73680f a30ae21b ef9aae80 87d2d369 6a462d75 da580d66 9ec4ff00 3ae42bac d1ce6cf1 e8c6a643 469ec146 d14ea2a4 a2195962 5dd8c93c 01ef4e52 02e64c06 ce314d9a 3f302f3d 181a8af3 fd56efee 9068196f 028a4072 334b4082 a48c7cd5 1d4d08ea 698131a6 9a87ed0b 920f1e94 f462cb93 4c0fffd0 dafb3928 549c7391 8ed4456a b136f04d 59e7bd19 03a9a423 8cbf4d97 920f7cfe 7550e315 abaca6db bddfde51 595c5034 21a61a79 e94ca063 954b9dab d6995246 c51c38ea 0d348e32 68013247 4a326a5f 286d073d 5777ebd2 99b3de81 a23cd292 58e4d3b6 8276e71c f5a94080 28cf523d f8349b25 bb15e92a e192dc82 42f27f2a 859d4b96 41df8fa5 098949be 8434639e 6a63bd50 36d3b7a6 7b535448 ca485cf1 e954eddc 77ee3763 134bb185 48229db8 da403cd2 9865276e 47dec75e f4ef021c bcc8c460 86e7a0c8 fce942a9 80bff106 1f9548b6 ce402586 083d327a 547e5a81 9273900d 4b6ba171 d744c869 2a5063c3 71f4a706 8c638069 5cd147cc 82976b63 38e2ac79 8809e9cf b50675d9 b714aeca e48f5640 a8cfc28c d3bcb620 9f4a5594 a1ca8a4f 39b047ad 1a8972db 503132b0 53de9a13 20e4e314 85d8ff00 2a4de7d6 9ea27ca3 f660f3d2 970b923a 7a035164 d2502ba2 7f93683c 7bd23142 4e38a8a9 28b0f989 e3942c81 8f007a53 1d94edc7 6001a8e9 29902d14 51400514 525002d1 4945002d 14945002 d1494500 14514500 145252e6 800a5a4a 280168a4 a2900b49 4514c05a 2928cd00 2d2d2514 862d1494 502168a4 cd2d318b 5a7a4362 fe3f7cff 002accab fa61c5f4 44ff007a 901d66ac 01b163e8 47f3ae11 f890d77f a900d612 7b007f5a e025ff00 586afa12 851d28a4 14b5050e aea34439 b661e8df d2b95ae9 7433fba9 07a11498 d1bd4514 95050552 b8666260 3dd72bf8 76abb552 ec6d5598 7543fa1e b40c9e07 0f12b0f4 a96a9407 cb99a2ec df32d5da 0415613e 58f355ea d9da1306 9a0328e7 a9a5562b f74e2a76 58f7613f 2a5088bc 9eb4c0ff d1e8dd4b 0e3820e6 abfd9632 72c49ab2 4e0649c5 33cd8ffb d48460eb 89831bfd 4560e457 4fac80f6 8b22f386 fe75cc66 81a13b53 2a4eb9a8 e8186682 49eb4945 004918de 42313800 918fa55f 5b380ae4 be4edcfa 638ef59a a4860470 69d81d49 a9926f66 162d3c56 bb885638 040ebebd 69e22b45 5decd900 e0f3fe15 470a39cd 184dadeb c629723e e162c87b 40a015e7 041faf6a 78bb8930 1231c8e7 fad67514 72202dcb 74248fca 0b800003 9ec2a317 0e15507f 0fff00ae a0a29a8a 42693dc9 8dccc78c e3e94c33 487f88f5 cfe351d1 4ec83950 a59bb9a4 a4a298c2 8a292801 68a4a280 0a28a4a0 05a2928a 005a2928 a005a4a2 8a005a4a 4a280168 a4a28016 928a2801 68a4a280 0cd2d251 40051494 b4005149 450014b4 945002d1 4945002d 1494b400 b4945250 02d14514 00b45251 400b4525 2d002d19 a4a2801c 0d5ab26d b7711ff6 c7f3aa95 34076cc8 de8c3f9d 219dfdf0 0d6528ff 0064d79e 4dfeb2bd 1ee5435a c83fd93f cabce67f be2afa12 868a5cd3 452d40c5 ae8b423f eb57e9fd 6b9caded 08fefa41 fec8fe74 9ec3474d 45145416 14c750e8 50f718a7 d453398e 2691792a 338a00a0 0b792b27 f144707e 95a81a3d 81dd8004 66b9192f e66432c6 7018e185 539e6909 0d9e08a7 615cedd2 e60793cb 88ee23af a0ab0467 92735c4e 977052e4 063f7b8a ec013556 1a63cf03 8a88d49d a9a45007 ffd2defb 3290cac7 21bb502d 9376ec92 6a7cf6cd 47e74591 f3672702 908a9a84 2a2c2445 1c019fd6 b8f0715d c48c93c3 2229ce54 fea2b87e 41228041 fc5cf151 9a90939c 9a8cd050 952320f2 96407a92 08fa5474 12718ed4 084a4a5a 4a062514 53c45213 80a7340a e4749561 2da573d3 1f5a4fb3 c9d78e99 eb4ae85c cbb90515 6cdb80d8 c9601829 c7bd28b6 0b82e739 c8c74c11 45d0bda2 29d255a0 b02a9c9d c48fc8d3 88806554 82b95233 fad17052 b94e8a56 c6e38e99 a6d32c28 a28a0029 28a2800a 28a2800a 2928a005 a2928a00 5a4a28a0 028a4a28 0168a4a2 800a28a4 a005a292 96800cd1 4945002d 14945002 d1494500 14514500 2d149450 01451450 02d14945 002d1494 b40052d2 51400b45 251400b4 b4945002 d390e181 f7a6528a 00f4d237 42c07753 fcabcdae 3ef0af48 83e6810f aa8fe55e 717430d8 f424552d 85d48852 d3452d48 c5cd6de8 67fd2987 aa7f5158 75afa31c 5e81eaa6 931a3ae2 70326a3f 301e94d9 09ce2a25 1839a948 b2c649a5 a87cc407 19e6a17b bc398d54 9238a00e 7ee2dfc9 b89ad874 71b93f0e 6b397f79 195ee391 5d0ea6c8 562b9523 7a1e477c 1ae7e41e 4dc32af4 ce47d3ad 512c891c a3871d41 aefe1712 c4b22f46 00d70320 c371d0f2 2ba3d1c9 b880c6ee df21fba0 e3834304 6f3cb1c6 3e7603eb 501b90df ea919fdf 181f99a9 a3b7863e 55467d7b d4d8a451 ffd3de4b 65421b24 914efb34 38c60f5c f5a9f1ef 462810c5 8913ee8e 718cd70d 32149dd3 d0915ded 717a9a6c be93dce7 f3a01145 b38e4d30 f5a90af1 d6984522 8654a857 cb70719e 08a8a8a0 07e501e2 984e4607 4a4a4a63 b96c5d6d 1c2f34c6 bb918100 01920fe5 55a92a6c 8cf923d8 98cf215d b9c0f6f7 a679b263 1b8f4c7e 14ca4a2c 5590bb9b d4d349a3 34531852 52d25001 45145001 49451400 51494500 1451d692 80168a4a 2800a28a 2800a28a 2800a292 8a005a4a 28a0028a 28a0028a 29280168 a4a28016 92969280 0a28a280 0a5a4a28 016928a2 800a5a4a 280168a4 a280168a 28a0028a 28a00296 93345001 4b494500 2d2d2514 01e93627 367137aa 0fe55c0d f8c4ce3d 19bf9d77 3a5926c2 13fec8ae 2b5518b9 97fdf3fc ea96cc92 80a5a62d 3aa4a16b 4f4838be 4f7cff00 2acbad0d 30e2fa23 effd2931 a3b37191 9a8b1918 ab151630 6a516545 b6908c33 051edc9f d6a616b1 9397258f b9a649e5 a49bdd8f 3daacc32 0901c023 1eb4c08a 682236ef 19014302 3d2b8fb9 52562971 db6b7d57 ff00ad5d a5cdb0ba 8fcb662a 339c8ace 1a4c6ad8 dc48f434 09a39750 248f69e0 83c1ad0d 25992f7c b0786183 8ade8b4d b60d9032 6af43690 c672aa01 1de80b16 c2f14845 2b4912fd e603ea6a b3df59af 5957f3a4 33ffd4ea 28a4a5a4 20ae575b 4c5cabff 00797f95 7575cfeb a9f24727 a122981c e9094c3d 29e0a81c 8a8e818f 99839047 5da327d4 d454ec13 d29c6270 bbf1c0a4 1722a4a9 d6176728 3008a70b 762bbb22 95d09c92 2ad255ff 00b20070 5bf2aa92 a8472a39 0284ee0a 69e888e9 334b494c a0a4a28a 002928a2 80128a0d 25002d14 945002d2 51450014 94514005 14525002 d1452500 2d251450 01451494 00514514 00b49451 40051494 b4005145 14005149 45002d25 1450014b 49450014 b4945002 d1494500 2d1494b4 00514514 00514514 0051494b 400b4525 1400b4b4 945007a0 68c49d3e 323b023f 535ca6b2 31752ffb d5d3682c 4e9ebec5 87eb5cfe ba317527 d41fd2aa 3b13d4c2 5a75314d 3aa4a16a dd9384bb 899b8018 64d53a5c d203bb6b fb34eb2a fe1cd557 d5ac8746 27e82b8e cd19a562 ae75126b 36fd5109 23d69875 ec7dc8bf 335cdd14 ec1737db 5fb9fe14 51fad557 d62f5ce7 701f4159 59a28b0a e5e3a85d f691867d 38a85ae6 76fbcec7 f1355f34 5160b8f2 ec7a9cd2 669b4500 7fffd5ea 281452d0 20ac9d65 375993fd d20d6b55 4bf4f32d 255ff649 fca80388 0ca0734c 3c9269e0 a8eb4848 278a0622 bed038e8 73530790 c5b76e40 eb512b6c 078e4e2a c2cec559 b6f1df1c 75a96449 764205b9 0430ea73 ff00eaa5 58ae0823 77f5a6f9 f29c6077 a04933e4 838e09fc b9a5662e 590c4492 438df8e3 34a2d832 ee6639ff 0039a956 d27763b1 81c6de46 7bd3bec9 b0aab127 71c1c1c0 19e99cf3 ef4ecc2c fa3182da 10c32490 455768e1 56e1ba13 c7b54be4 47b4176c 1dbd323a 8350ccb0 24c3ca3b 9383fe22 84bcc693 ee55a2a5 9b6995f6 1c824918 f4a60563 8c0ce781 4cbb8ca2 a558a473 85534f5b 595829e0 6ee9cd17 25cd2dd9 5a8ab22d 653e98e7 9271d3f5 a71b5214 9073c061 8f4ce334 ae83da47 b94e8abe d6613ef4 83820607 5e6a131c 407de193 9ebda8b9 516a5b15 68ab205b 80739e94 bbedd7a0 27fc28b9 7cbe655a 70562a58 0e075353 09507257 279fd6a6 cb1563b4 0f90743e a463f1a6 8976451a 4a9e41e6 48d27dd0 cd9c1393 c9a4f21f b62ad424 fa11cebb 90d15388 0f723a66 831a7383 d0e29fb3 9073a20a 2a609177 6a51e563 1f4f7a7e cfbb173f 9105254f be207e51 de955880 36ae7ad1 ecd770e6 f220dad8 ce3a51b5 872454df 3632131c 0e7e943e f7c06c0c 9a3915ae 1ccc8f63 63278e33 4e10b13c 53c2c98f bd81fe7f c290a363 2ec71806 ab917617 3f988d18 108939c8 6da7d3a6 4526c5c6 738e29ce 85622724 e1b1edc8 aaf59ecd dd15badc b00a2b82 b8c86046 7a6289c4 5d63c72c dd3d33c5 57a29377 1a560a28 a2a46149 45140c5a 4a28a005 a2928a00 5a2928a0 05a29296 800a28a2 800a28a2 800a28a2 80168a4a 280168a4 a5a00edf c3cd9b22 3d1cff00 4ac8d7c0 fb5391dd 54d69786 dbfd1a45 f47fe62a 9f8857fd 2323ba0f e66aa24b dce5969f 4c5a7549 42d14946 680168a4 a33400b4 52668cd0 31d45251 400b4525 1400b452 628a00ff d6ea6929 68a420a6 ba8642a7 b8229f45 303cf480 ac4376e2 909048c0 ab3769e5 5e48a3b3 1fd6abb1 6239140c 68628c48 039f519f e748ae55 597b363f 4a46eb4d a065a851 a6c0dd82 5828e3d6 ae8b0083 73cb8183 9c7d2b32 32d8214e 3d7f0a71 0bd09207 1de87094 b664b996 84030313 9036e476 1c1e3bd2 496b044d fbe73ced 3d467049 c9c55409 164739a0 98d47033 9147b297 590b9bc8 b1b74f56 196246e2 0f5e9da9 ff006ab3 5236a11b 4e7a0e78 c5522ea1 ba7346fc 1c2ae0ff 008d2f64 bac82efb 124d3869 d6655231 ebde9567 9010ca9c 827f5a84 3c9fddeb ff00eaa4 3e69e78e c6b4f671 21abee4e 6eae4fa2 e07f2a8c cb3b2f04 6073c0c5 371201b7 760e7a7d 693cb73f c5daa953 5d89b450 e8848665 b7326c19 c649e055 85b58c81 be5da304 641ea41f af4aa68b 1b23bc8f b587418c e7f1a6a7 978c9c67 d2a14536 68f43456 cecc82cd 2e70a18e 08e3d7eb 4d692c23 2a918c80 d92dedfc cd510d1e 06e1923d 29f98f04 ece9ed56 a0bb85df 626492d5 142ec2d9 52a4e3bf af5fd292 5b989e37 58a2dbb8 2f4c718c e7b77aae 66208006 003d0d33 cd38c018 e315368f 70d7b0f0 f2b442df 680a0e72 473f9d3f 749b7014 1f976673 d79e0d40 667273fe 78a6991c f7a7eea0 b4898f98 7ab0e80f f9e294a3 8ea4f079 c7bd572c dea69b9a 39d072b2 c15dcabb dfb1e3d3 141580f3 9c557a28 e75d8393 cc989847 1d7df9f4 a4322018 40464106 a1a2973b e83e4458 f3900385 f4a6b4ee 4d43494d d4907222 432b1a43 239e49a6 5152e4fb 8f950e2e c7a934da 4a5a9b8e c1494b83 d6942b37 dd04d01b 09495208 a4270073 4a2173f9 66a9424f a0b99772 2a2a736e c012c718 207e79ff 000a4f2d 4639f5a5 6652d55d 10d152a2 a1525cf4 e94fdd02 9c819c37 e94d43ad c872e962 bd153798 98fbbce0 f6ef4ef3 f1f757d3 f4a7cabb 8733ec44 2373d01a 363727a6 29ed3b37 61d734d3 2b7e4314 350ee0b9 8468d941 2dc631df d6994f32 39041270 7afe14ca 876be852 f30a5a4a 290c28a0 027a5140 052d3bcb 7f43ff00 eba51136 403c64e2 9f2b1732 19454a21 7233c629 c900781e 62ea3663 e53d4e7d 2869adc2 e8828a7a 283cbf4f 5a79108e f9a2c5a8 90d1536e 881c8069 8cc08c0f 5a2c163a bf0d30d9 329f507f 9d37c44b fbc523ba 1fe74cf0 c91e64ca 7d17fad5 af10ae3c b3eaac29 c3733671 229d4c14 fa9285a2 92968012 96928cd0 02d14945 002d1494 d0792280 1f452514 0c5a3349 45007fff d7ea6968 a2900514 52d0238e d61765e9 6fef006b 38ef20fa 56debc98 92393d54 8fcab130 48c934c6 46dd2995 21fbb51d 004b0046 936c9800 83c9e838 ab4134f0 bf331dd8 fd7f2aa7 110b22b3 74041357 566b38ce d68cb104 e491c9a0 19217b08 5c796bbd b8c6327a f18e7bd4 3e680434 911dd961 8c0efcf4 3531b98d 517c88b2 71cf1d31 f8734c77 b9525d90 677023db a8c0c7e5 40843713 14dab0f5 4c647b77 aa970f29 977c8815 9c861fe7 d0d5d537 e71b001c 951d38cf 2466aa4c b70002f8 3b97185f 45f5e3b6 284047fb d0c01383 9c7f8520 8e4619dd 8e3f9529 47033b8f ad20894b 105b9c9a e8b332b8 9b31f333 73c1cd21 58c11939 1f5f6a0a c4a319c9 e69bfb91 20382c9d c54c9d90 d2b89fba c0cfa76a 52625915 80dca3a8 a4668fcd 2c8b85ec bd69cc0e d52171b8 71cfbe3f 9d4f35d5 8d1456ed 8c77fde9 745da3d2 9be61c7e 18a94973 1ba91d4a b673d3af 6a8fc972 703d714b 958f9d2d 98d91fcc 72f8c67f 1a8aa711 0c649ed9 e39ef8a5 f240e59a 8f672239 d6c57a2a 7f2e3ce3 3dcfa521 11f4c8e9 fae68e47 d439c868 a99cc582 13f0a457 450060e7 9cfe3472 abdae3e6 76d8869e 11fd0fa5 4866e30a 31c638a4 f3dbae06 7d4fbd16 8f715e5d 86147eb8 2297ca7e fd2832b9 ef4d2ede b4fdd0f7 8904249c 03ce48e7 da9c215f e26c719a 80b31ea4 9a6d1cd1 5d02d2ee 58db1a9f 9bdbbfb5 37318e06 3bf3f871 50d14b9f b21f2799 36e8f1f8 0e07a8a5 32a60855 c1ce474f 4c557a76 d23b7bd2 726f4049 2771e641 e9ebfad2 24ae9c29 f7a3ca7c e1863ebc 7f9eb4be 530193d3 9fd29a52 dd0e524f 713cd933 9cf39cd0 25907f11 e98a5f25 80c923a6 7f3a7f92 3a16c1cf e95569b2 2f12be49 eb454db6 3c1e79c1 efde9dfb 853fdea5 ecdf563e 7f22bd15 63743bba 700d2095 5474c9c6 39a39175 61ccfb10 ed62db70 73e94bb1 c1c60d4a 6e0e30a3 18c734c1 34814018 e295a3dc 2f2ec218 d80278c0 c7eb4f58 59c02a47 3519763c 67b629a0 91de97bb 71fbd62c 083fbc7b 51e5c609 dcd8e9c7 d6ab514f 9a3d85ca fb9695a0 8f0c577f 2c304f6c 707a50d2 4585c765 03a7719c ff008d55 a2a53b3b 9562c99d 79e33c83 9fa537cf 2000a318 cfeb5051 4fda3172 2261338e 87dbf2a6 f98d9ce7 df8a8e8a 5ccfb8f9 50f2ec78 24d36929 6a4a0e69 6928a002 96928a00 e97c347f d26453dd 3fad6a6b ea7cb8cf bb0ac6f0 e7fc7f11 ea87f98a dfd707fa 3c64ff00 7ffa5543 7133cf7b 9a7534f0 c453a931 852d2514 805a4a28 a005a434 525002d3 47534b4d 1d4d003e 8a292818 b4525140 1fffd0ea a8a5a290 098a7525 2d0061eb a99b647f eeb63f3a e6142119 635d96aa 9bec5fdb 06b8c528 07cd4c04 c0c102a2 a9b20938 e2a1a003 3839ad21 78c992b1 8c6771c9 ef5994ff 0030fb55 2b7513bf 4349afa5 28151406 c9cf3c62 a9996e0b 16661923 1cf3c0f4 f4a87cc7 63827ad3 f61e0927 ebf866ad 453d886d adc7979c 9c8908e7 3e9cfad4 6416c6e7 27ff00af cd34a2e3 938e3d69 3f740e3a e3bd165d 857626d4 0b9c8e47 7a3f759c 9f6e9fad 2e611ce3 27f4a6ef 4f4ed8e9 4f40d47c 2d124fb9 e3f31391 b7a54ed7 16ed1848 e1c1db82 78ebf97f f5eaa991 4f41dc1f ca9c8ef2 308e3039 c81cd45a 3dcad7b0 2b4ab134 21460f24 f7a033ed 45006464 73f9d349 91876c01 ff00d7a5 6f3b39ce 09c37e75 6a2b744b 6f660566 0b9ce062 908909fb fdc73d3a f7a4d926 796e49c7 5a4311da 0e7b74aa d7b13a77 1446a0e1 da9a561e 79ed4be5 a73cfa73 ed4c758c 2fca7269 3db61adf 7240d123 0319c104 104d3663 1100c7fd e6ed8e33 c527ee42 fbe3f5a5 f323e4e3 be702a1a b949dba1 5f04d285 63c01560 4e173851 8a8fce61 c8f6fd28 e58f71de 5d86f96f fd293cb6 c67db34b e6367753 77b7ad2f 743de241 0373bb8c 100faf3f feaa558e 2126d639 ebed5119 243d58fe 7e94c249 39350f7d 0d62edb9 7c45061c 1c03d172 7da980db 8653818c 739aa749 53ca69ed 7b22f3bd bee7c720 8e31550b 12c091d2 994534ac 44a6e458 f3864657 80738a60 99802001 c8c7f5a8 a8ad3da4 8c791129 99cfa0e9 dbd3ff00 d54df364 ce771a65 153cccab 214b13d4 9a4a28a5 71852514 50014514 5001494b 45001451 45200a28 a2800a28 a2800a28 a2800a28 a2818514 5140052d 251400b4 9452d006 df87c9fe d151eaad 5d3eb393 68b9ece3 f91ae4f4 36dba945 ef91fa1a ec35719b 239ecc0d 5477133c e1f89187 bd14b371 3bfd4d36 93dc62d1 4514804a 28a4a005 a2928a00 5a68ea69 6917a9a0 07514945 03168a4a 2803ffd1 eafb514b da8a4025 3a9052d0 041729e6 5bc89eaa 6bcf8f5a f47c6457 9fcc3c99 dd7d0914 c08901cf d698e30c 454a1cb3 0cd3651f 3d00434f 542ddf14 da373018 04e29ab7 513bf424 f28e01eb 904f1ed4 86203ab7 a5459357 05bc1e50 7328dc57 3b78eb55 75d89b3e e57d9181 8279cfe9 4dc463f2 fd6ade2d 39e7032a 47249c63 91d28df6 68c36038 e7279e41 cf1cfe14 73790ec5 43e5e085 073eb53c 5730471a a3440919 cb7739fa e4543148 a892230f beb8c8f6 39a82a5b b8d2b13c b3090a15 5dbb40fd 291ae5de 613b0048 c7b0e2a0 a4a43243 23741802 93cd93d4 d3292ab9 98ac852c c7a934da 5a4a5700 a4a70563 d0134983 4582e252 54823727 1823eb4a 6171c7d7 f4a7cac5 ccbb9151 53792e41 23180334 ef23f849 e69fb397 6173a2b5 1560c483 20b73cd1 b615efc8 008ef4fd 9b0e7456 a2ace600 7079009e 83b76a04 88070b90 463f1a14 17562e77 d115a8c1 ab9b8820 84efde98 25943ed5 ea323934 f91771b7 2dec4263 70718ebe 9cd2885c 8c9e00ee 6a6459c7 4c0e9fa7 2282b2e7 25b07239 1d39aa54 d6f66439 bdae462d dcf39029 7c820e09 e738a529 d99b3c1e fdc53592 1033bb71 a1c63d83 99f71b24 4235520e 7248fc8d 455618c0 ae42f201 383edfa5 11ca8a18 127942bc 773dab13 6b6857a4 a9256569 0b274e3d bb7b5474 8028a28a 0028a28a 004a28a5 a0028a28 a0028a28 a4014514 9400b452 0a7004f0 39a004a2 9c11c8c8 0703ad3b c993a639 c818efcf 4a2e2ba2 3a2a6fb3 cbdc01d7 a903a75a 7b5ac8a3 71c6301b bf7a5742 e7456a2a dfd9d724 6ee8d8cf e1ef514b 1c6806c7 dc7bd171 a927a10d 14514ca3 47496dba 8c27fdac 5771ab9f f8974ac3 82307f51 5c169cdb 6fa13fed aff3aedf 526692ca e54f40bf c8d34267 9d392642 4f7a5148 ff007f8a 050c62d1 45148043 45069280 1690d145 002d357b d2d22d00 3a8a4a5a 06145251 401fffd2 eb28a5ef 4521894b 4514082b 88d51365 f483d4e7 f3aee2b9 2d7136dd 2bff0079 7f953031 7630e7d2 9d37634f d8ec396e 3ad35f98 c1a6057a 4a5a2900 da39c668 ab76f204 46253705 20f6fa50 052a9161 95d4b229 2075ad44 bf01d730 8001e71c 75aaf3de cee9b785 53c71e82 8b059954 5ace5b68 539c95fc 476a70b2 b8237101 4609c923 a0eb5299 ae4f2085 e72318e0 f4eb485a e5b8329e 091c1f5e bf9d5f23 ec473a2a ac4ec0f6 e9d7de9d e41ce09c 52951ddf 3c74fa76 a42b09ef c71cd5a8 a5b92e4f a08234c6 4b76cd28 5898e4f0 38efeb4a 3c851c72 7d7ad34b c407cabd aaac9761 6afb8c7f 2b6fc9d7 35248c8c 55a28f66 d193ce73 ef4d32a8 fbabdf3d a9a651fd d1dff5a8 76bdee52 bed61c18 9002a938 23156112 e9c05440 4f2a01ed 8ebd6aa3 4cec3071 dbf4a70b a9941da7 6e493c00 39343a8e fa312877 45dfb35f 15dc1428 233c91cf ff005e9c 2c5a1c09 a5dadbf1 85ef9c74 efdfd2a9 0bdba008 f31b9057 9e783559 9998e589 27dea5cd bdd94a08 d25b3017 12c9b490 7af0320f 4eb51cd6 f6b1a315 977b6d52 391d4f5f 5e959f45 4dcab132 f921016c 96cf4a5d f081c827 8c541495 6a76e84b 85cb0665 ce42e064 1c51f692 3a2f623f 318ed55a 8a1d46f4 0504893c c6c63e9f a5377b6e df9e69b4 951765b7 7dc7991c f1b8fe74 d2493934 9451762b 2128a784 7638009a 708652bb c29c7ad2 b85d1151 4e08c5b6 0049f4a7 f9320e48 e319fc28 0ba22a2a c7d9a4e4 f180db73 ef4f16c7 66e39e46 46075e33 4ae89e74 54a2aeb5 bc40edde 3191f367 d7afe469 a63b6438 2dbba8ce 7f2e99a2 e1ceba15 28ab6c6d 76fc9f78 0039ce09 1dff001a 3ce8013f 2f1b8903 1ea28b87 33ec5400 9e8338a7 2a333050 393567ed 40021500 04629a6e 5c839192 48393edf e34aec2f 2ec31209 5f90303d 4f03d29c 2da5c65b e5e0119e f9a3ed53 6368200f 4c7e3511 9646c6e6 271c51a8 7bc4e2d2 40fb5fd7 1c73cf6a 85902843 d7775f6a 61763d49 3494ecc6 93eacb9e 4db7cc4c 9c03c0ee 6973688b 8539273c 919ec71f d2a9514a c2e4f32d 3bdbed22 35e703af af7a6098 ab71f743 67150514 587ca89f ed0766dd a3a639cf b0fe941b 99892c48 ce4738f4 e9505145 90f95121 9a562096 e99c7e34 cdcc7b9e 69b453b0 d242d145 140c28a2 92802c5a b6db98db d1d7f9d7 7d78bba0 b81ea8df cabcf233 8753e845 7a44eb91 201dd1bf 9552133c ce4c6fe3 a5028907 2281498c 5a292969 009494a6 92800a28 a4a005a2 8a2800a2 8a281851 4945007f ffd3eb68 ed451486 14514502 16b9ed7d 331c527b 915d0d64 eb29bac8 9fee907f a5340722 150e0b1a 5eb1103b 52298f1f 375a7295 2180e9da 9815a929 69290121 8241918e 5480477c 9a6ab940 c98ebc7d 315335c3 33162073 b7ff001d e95598ee 62dd3273 400a6573 51924f5a 5a4a2e3b b14bb1ea 4fa534fb d1453b88 4a4a5a29 009494ec 13d29cb1 b38c8f5c 7e740ae4 545595b6 772a0119 39fe59a0 5b3b720f 45dc73fa d17279d1 5692ae8b 640db5df a1c1e31c 7ad37c98 5782f9c8 38c11d7b 52b873a2 a5156f6d afde63d8 703f5ed5 1ab4226d cca4a7a7 e145c14b c8af4a46 383d6ac9 9a300845 c6571da9 7ed5b725 17049ce6 8b8733ec 57f29c82 70703934 ff00b3ca 392b819c 1cf6a95e f2571860 31cf6f5a 80cd2118 cf518346 a2f7877d 9db38c8f e21d7fbb d697ecec 54ba9e02 86e78a8b cd9318dc 7ae7f1f5 a69663d4 9346a3b4 bb967c88 d137c84f 0d838a51 1c2115cb 0cf0483d 71cff4c5 53cd1458 5cafb9a4 64b35762 aa3a0c70 3140b9b6 44d8072b 8c103ae0 d66514b9 49f64ba9 aa6fe312 6f552303 1c5577bd 63198d17 00f7ee39 cd51a28e 54354a28 7a4af1bf 98a79f7a 779ce14a e7a8c7e1 9cd45455 58bb2243 2c8dd589 e73f8d33 271cd251 40ec1451 45030a28 a280128a 28a00296 8a280129 68a2800a 28a2800a 4a5a2801 2968a290 09452e0d 28462700 734c06d1 52889cf4 04f7fcea 48ada596 61020f9d ba0a2c17 457a4a91 e331b146 ea0e0d37 1480415e 9439c1fe f2ff0031 5e6f5e8d 6e729013 d0a2ff00 2a6819e7 170db986 3b5462a5 ba004840 18c13510 a18c5a28 a2900869 2968a002 93bd1450 01451450 01451450 30a28a28 03ffd4eb 68a28a43 0a296928 10b556f9 3ccb4957 fd93fa55 aa6b0dc8 57d46280 3cf55b6e 78cd3b76 e6ce3191 43662761 dc1c7e54 9bcb30cd 5010ed24 9c76a465 2a707eb4 f6255881 de98cc58 963d4d21 0cab06dc e0924fb7 e74470f9 833b80e7 1cf5a530 aafdf7cf 0718c751 49b25be8 80dba06d bbb8ce37 67b530a4 238ce4e0 f39e323a 53c2419c b36071d3 f5a8a4f2 f6e13d7f 1c62a44a e3dbeca3 e65e7a70 73f8d355 e018c8c8 04f18ec7 f1a8dd95 9571d40c 1a8a9a43 51259245 620a285c 54df6c6c e4228390 7f2aa949 4ec87ca8 93cd70ec ea705bae 29ab2327 ddf6fd29 b4531d90 e6964618 2c706985 98f524d1 49405909 452804f4 19a528c3 820d0171 9454a229 0b05c119 38e69c2d a7233b4e 08c8fa51 71392441 495756ca 627e6c01 d339a6fd 9727e56c 8e79edc5 2e642f69 129d1573 ecf16d2c 641d0103 f98a8c24 027d8edf bb07a8ff 00eb53b8 73a656a2 ae86b551 819e983e fd3ffaf4 a6e2d979 8e2c1041 14afe42e 67d8a001 27029e91 48ff0070 1356d6ed 5000b18c 8cf3d09c 9fa55713 bafdde3e effe3bd2 8bb0bcbb 0d689d14 3b0c0271 d6956de6 7e83d3b8 ef43ccee a55ba139 fc693ce9 7a6e3d31 4f50f7ac 4eb6526e 01f8c9c7 1cf5a67d 9b09b99b 92a4803d bb542647 2305891d 6a3a5661 697565df 220c92cf 8031c719 c54616d8 37cc491c ff004aad 4a013458 7cbdd965 9e2da429 ea17181d 08a64ae8 e495eec4 f4ed5184 73d01fff 00550237 3c8069a8 8924ba8c a2a6f224 c671d0e2 97c838ea 0f19e2af 95f61f3a ee41455b 16d82373 0c1f4a69 85012377 4cfe94fd 9c85ed22 56a2adaa db83973e 9c0e9ef4 66d81190 7be7147b 3f30e7f2 2a52e0d5 8f363c67 68ce00e9 e9479e17 1b1718a5 cabb8f99 f620da7d 29c2362d b3183ef4 ff003d80 0071818a 6bcceefb cf5a5688 7bc385bc 87b7eb4f fb31c63b f1f4e4e2 a132c87a b1a69663 d4d55e3d 8569772c 0814300c dd491fa7 b9a7a8b5 2a700e42 0ceef5c8 ce3f0aa7 4950dabe 8524fab2 d830a81d 0e08edd7 8a4f3210 3a13c103 3f5e2aad 155cfe42 e4459f3c 0390bc71 c74e948d 72cdce00 eff9d56a 5a5cec7c 889bcf7e 838c0c53 3cc7ceec f23bd474 b49b6c69 242e4d26 6928a450 b5e8764d 9b4b627b a2d79e57 a1e94049 a740c4fd d5c7e468 4267057e bb6e645f 4761fad5 515a1ab2 edbd997f db3fceb3 c50c0752 51452181 a4a5a4a0 02929692 8016901c 8cd1483a 5003a8a4 a2818b45 251401ff d5eb68a4 a5a40145 145002d1 494b401c 2df47b2f 2541fde3 fad55d85 793c56b6 b29b2f77 0e37006b 34a7072d d3b55011 baee703d 78a6baa8 191dc9fc 853a4e80 8a1f6608 5f51f90f f1a4c440 69296942 92091dba d03194a5 18751db3 f8539919 7afb7eb4 f1332fdd feeed348 4df61821 91880072 4e3069ab 13bb155e 48f7a91a 7958e491 9c83d3d2 a3576424 a9c13c51 a8b52416 b2104e47 18efeb4f fb211f7d c01922ab 9663d49a 6f346a2b 4bb963cb 840396e7 04fe238c 52ecb404 9763d7f8 7d2aae28 da7d29d8 397ccb45 ad3a0190 3a71cfe2 698668ba 2a00383d 3d3eb50e c6e98a4d 8c7a0347 28b95772 7fb428e8 83a93e9c 7e02982e 1954aaa8 195da69b e4c873c7 419fce94 42c719ee 714f93c8 5688d333 97dfc649 cf4ee3eb 41b89ba6 f3d31c71 4f16ec57 764629a9 1071b890 30714fd9 bdac3bc4 63cd2c98 dcc7818a 8aacec8c 0eb9ea3f 1ed4e02d 94f249a6 a987325b 229e28c1 ab6cf0e4 6d1c73db d4535a60 7a0c6463 8a7cabb8 733ec57d 8c4ed039 a0233741 9ef53f9f eddf77e3 49f687c6 001d0afe 06869771 a6efb0cf 224ce0fd 294db481 ca719033 4c7919ce e3d69858 939359ea 6b78f624 11a90093 81521810 315271c7 7e39aad9 3d0d2500 a4bb0f7d 9c05fc6a 5c5b83b7 39191cfb 556a2a93 b19c95cb 22483f88 640040fc f8a533c7 8f9548e9 d303b0cf 4fa555a4 a7cec5c8 8b06e33f c23a93f9 d35e6771 838e98fc aa1a29b9 cbb89423 d89bcf97 a838fa53 3cc7f534 da4a9e66 5590b934 51494861 45145001 452d1486 1494b450 0252d145 001494b4 5002514b 49400514 51400514 94b40094 5145002d 7a1e84c1 b4c881ed bbf99af3 baeffc3a 4369a07a 3b0a4073 1ae4416e e57f57fe 95882ba4 f1003e7c b9f55fe5 5cd8ab92 1445a296 92a0a0a4 a5a4e280 0a4a5c8a 4268003d 2917a507 a503a500 2d14e542 dd287528 326818da 2a68e2de a1bd69fe 40a571d8 ffd6eb28 a6d2e690 0b452519 a005a5a6 fb52e680 39dd753e 68e4f504 561e221c 9ae9b5a4 dd6aadfd d6fe75cf c72dba28 deb93df8 aa115c8c c79a42e3 6e07f740 fea6a6c8 657da303 9c0aab43 0b0dc135 2a2c8a78 1cb023f3 a603b4e4 53fce6ed c7f9c535 6ea277e8 0c1d8630 3181fa53 040c5829 e39c52f9 afd2a428 e4e7775a 692e80a3 27b11f90 dde97c90 7be071d6 9360eed4 842fa8e9 fad3d3b0 f925dc74 49019b6c cc427a81 563cbb00 8c4bb13b 495e0f5e d9e3d6a3 83ec9e59 3719dd91 803d29c2 6b71c6dc 0c329207 241e9d4d 4015c188 c4536132 13c107fa 548ae5fc b8d412ca 318fa126 a47ba84a ed8e20b9 1d78cfe1 55fed0c2 e0dc28ea 49c7d7ad 35a09ab8 d33f18c7 5c67f0a4 370dd80c 73fad447 af14955c ec5c8bb0 e12b8180 71486472 73b8e69b 4953ccc7 640589ea 6928a290 c4a4a5a2 801b452d 1400da4a 75253010 d21a5a29 00da2968 a004a4a7 62928012 8a5a2801 28a28a00 28a5a4a0 028a2968 0128a5a2 80128a5a 280128a5 a2818525 2d140094 52d25200 a28a2800 a4a5a4a0 028a28a0 02929692 800aee7c 347364e3 b873fc85 70d5da78 60e6de61 e8c3f514 80a3e211 899c7b29 ae58575f e221fbd3 ee83f9d7 1e2ae5d0 511d4514 54143946 4814f950 2a640a62 fde1f5a9 e73f2503 434a8f2f 3ed557bd 5c63fbaf c2a99eb4 90303d28 1d283d29 074a622e db8ca9fa d3271814 fb73843f 5a8a7391 4865983f d5ad486a 38c811af 3da90baf ad22ae7f ffd7ea68 a4a29087 668a4a29 80b9a5a6 d2d0053d 4577d9c8 3db3f957 19827a0a ef255df1 321ee08a e1d77024 2f5a602c 408ca9ef 5588c715 6d7707f9 aa071872 3de98115 253b156a 358488cb 632490d9 3dbd6901 4c647229 0f353481 46ddbdc0 27eb5163 d281dc6d 2549b1bd 0f34be5b fa7f9345 98ae88a9 2a710484 81c0c9c7 26a61652 94df918d bbbbff00 850d5b70 bdca5495 6e6b7080 b2e71852 01ff0068 66ab5003 0d14ea4a 006d2629 d498a006 e28a7629 2801b453 a92801b4 62968a60 36929d49 4086d14e a4a006d1 4b45031b 452d1400 9494ea4a 402514b4 530128a5 a4a0028a 5a290094 52d14009 452d1400 51451400 5252d140 09452d25 03128a5c 514804a2 9c119bee 827e9561 2caf2419 485c8ff7 4d0055a2 b4d746d4 9c022161 9f5c0ab4 9e1dd458 fcc117ea dfe14018 3457509e 18b82712 4a83e809 ff000ab2 9e178bfe 5a4cdf80 028038ea ebbc2e78 9c7fba7f 9d5c4f0d d801f317 63f5c7f4 ad3b5d3a d6cf77d9 94a938dd c939c500 607893ef 29ee53fa d7182bb5 f1281b23 3df04571 429b121d 45145494 28eb52c8 432e3350 d3680272 ea576e6a 038cf145 250303cd 1451400e 0c474a37 134da280 17268e69 28a00fff d0e9e969 28a042d2 d251400b 4b494b40 057153a1 4b978c71 f3115dad 725a9a05 bd6f4383 4c45420a e0eecd45 27dea7b0 8c0f97ad 0f8dca4f 4a632be2 ac44f12a a890670d 923d4629 8efb863d c93f8d35 53706390 368cd211 3ce63902 f931ed19 38e9cff5 a6195947 ddc67a54 9e5dc40a 18ae36f2 09f7a66c 925c3311 8e99a717 2be844ad f687edb8 607200c7 6348d04c bd08ee3a 7a1a5224 c65a420e 054585cf ccd939e7 269b53ea c85e43f6 3139de78 c107a75a 6b220c03 213fd299 b6103927 35610d98 e5d58e08 e9e9c7bf d6a5c5f5 65a22220 d992c492 b9fc6a19 7cb392b8 ce7b7d3f c6ad9922 7f9114e0 6fc038e0 1e9d6a9f 96fe8685 163d16ec 8693153f 92ff00ae 3ad2f93c 73e87f4a be563e74 57c5262a d18369c3 3771d3de a75b5b7d 9b9e500f 231c7634 9a6b7052 4f633a92 a78c4473 e61e94ed d001d33e d4d46fd4 4e56e855 c52609e6 ad349174 55ed4e17 3b4e5500 c1cfa7f2 a395770e 67d8aa11 fa814851 b76dc734 ff0031b1 8f6c5217 62dbfb8a 5a1a3b5b 41441291 9c6297ec edd3be71 4d32487f 88d31999 b9249abf 74cad2ee 4be48191 9c9c1fcc 5279708c 12f9fa54 3494aebb 072bee4a 44218807 23919fcf 14f49230 ae18fde5 0318ee08 aad462a6 e697e83a 52864629 f77271db 8a8e9714 629084a4 a7629551 dbeea93f 4140c652 55c4b1bc 939485cf fc04d5a4 d17537e9 0919f5c0 a00c9a2b a04f0e6a 2df782af d5bfc2ac a785ee4f 2f2a0f5c 026811cb 628aec93 c2d1e7f7 9393f45f febd594f 0d5901f3 3b93f503 fa500709 8a5c57a2 2683a629 cf965b1d 724d584d 2b4e4e90 273ea33f ce90cf33 c54ab04d 27dc466f a026bd45 208131b1 147a600a 97be476a 00f324d3 2fdc6560 7c7d2aca 683a9b90 3cac67d4 8af44ff2 697b7d68 0b9c2278 6afdbef3 22fe24ff 004ab49e 17908cbc e3dc0535 d8ff0091 473f88a7 611cba78 62d8105e 5723d801 fe35653c 3ba6afde 0cd9e993 fe15bdfe 4d27bf63 401969a3 698831e4 a9c7ae4d 5a5b2b34 39485011 fec8ab24 8519278f 5a81ee6d e3e24910 7d48a404 aaaabf70 01eb8a5f e559efab 69c84e67 5cfb73fc aaa3ebfa 6a64ab33 7b053401 b5d3e947 b1ae71fc 4d66bf72 376fae05 557f1476 4838f76f f0148675 dec7ad27 5fad710f e25bc390 a883f33f d6aabebf a938c6f0 3e8a2803 d07afd45 1fcebcd1 f55d464f bd3bfe07 1fcaab3d cdc49feb 2466fa93 401d5789 8a18e2c1 1905b23f 015c48a9 c64e7350 77a631d4 52d2548c 29b4ea56 4daa1bd6 8019451d a9281851 45140052 918e69e2 325370e6 98cc5860 f6a004a4 a5e4538e 1a803fff d1e9e8a4 a5a042d1 45140851 4b494b4c 02b9cd65 31323faa ff002ae9 2b17594c c48fe871 f9d00609 6f970169 8fca0a95 4c98f971 8e95191f 21f6a604 1534032e 57d54fbe 78a88d03 20e47148 6586799d 70d21208 e99f4a85 800081eb c5329299 575d8976 c40024f2 452e601d 89e950d1 55cde467 cbe64be6 463eead2 79c4f51d aa3c5262 8e662e44 4c677230 714c32b9 18cd3693 04f4a1cd bea1c8bb 0177f5a6 92c7b9a9 d6de67fb 88c7e80d 4cba6df3 7dd85bf1 18a5763b 2285262b 61745d41 bf800fa9 153af87e f0fde641 f8934867 3f45750b e1b7fe39 80fa0ab0 9e1cb71f 7e563f40 050071d8 a4c57709 a1e9a095 25988ea0 b7f85585 d2b4b438 f2d4e3d4 e7fad007 9f6280a4 f419af48 16da7443 2238c7e0 2a6125ba fdcc7b60 530b9e6e 9697327d c89cfd14 d585d275 17e9037e 3c7f3af4 447df9e3 1838a928 03805d03 526ea817 eac2ac2f 86af0fde 741f99fe 95db5250 2b9c92f8 60ff00cb 49c7e0bf fd7ab49e 19b41f7e 473f4c0a e8e9690e e622787f 4d5eaacd f56ff0ab 29a469a9 d2053f5c 9fe75a54 d2ca3ef1 0280204b 3b441f24 483e8a2a 70aabd00 1f4a81ef 2d139795 07fc0855 66d634d4 eb32fe1c ff002a2c 234a8ac3 7f1069ab c0666fa2 9feb55cf 896d89fd d45237e4 3fc6981d 1fbd15cd 9d6ef188 1159b024 e06e38e7 f2a89f53 d6763482 044551b8 e79feb40 1d47d3f2 a3f9571d 0deeb577 10996455 466db900 6739c546 23d72701 ccf8caee fbd8e3f0 1401dafb 9eddea36 9234fbcc 067d4d71 0f637078 9eec7500 fcc4f5fa 91502c16 3b4191c3 7c8dd5b0 7703c700 9a433b67 d42c63ce e9907b6e 1555f5bd 31073283 f404d732 1b4a8cb9 91158ee0 542e480b 81ff00d7 cd49fda1 a520f920 ec47dd1d feb401b2 de22d3c1 c2ef6f70 3fc6a2ff 008484bf fa8b6918 9cfe9d7a 562c12f9 11f99f66 05708db9 8e391c67 f3ab12ea 57d0211e 42aa2b15 cf2467ae 32280343 fb5f5393 fd4da606 07de3ebf 9552b8d6 3558632e e235c36d 200c9071 91fa565c 9abde49c e557e5db c0ed54e5 ba9a6565 90e7736f 3ea4f4a0 0e8fccd7 662bfbe0 a09e4ae0 63f4e78a 81ad7567 6c4b7583 9c6371fe 9580d733 b00a6462 00c01935 1124f539 a02c6ccb 628b16f9 6ed58e0e 0039e71e e6aada25 9e164b86 19dd8653 9fbbebc0 acfa290e c6aaff00 64ac6039 777da724 6719ede9 54aedede 49775aa1 44c0e0fa f7a8446e 54380482 703ea2a7 fb0dd1da 7cb6c38c 838edd33 480a9495 b8342bad c8acc837 827a93d3 f0a54d17 281da65f bfb5b1db 9c503b98 5456b6a9 a7c76050 46e5c367 923d2b26 80128a28 a007a727 1ed501ea 6a51d6a2 6fbc6980 ea281452 18534e71 834ea463 91480423 1c8a434a 72383487 ad03128a 43d29680 1e8e50e6 924605b2 29410460 d33a1c50 0381cf06 90823914 ec06e453 41238348 0fffd2e9 69d49450 21696929 68016969 b4b4085a cfd51775 9b7b106b 429aeaae a51c641e a2981c5a 0c8e5b14 6d1caaf3 5d91b7b4 8ba449f9 0a9a2f2c 8f9540fa 53b81c48 81de11b5 18b6eec0 f4f7a55d 3ef5b911 37e3c575 f7576b6b 8054b6ee 98a96099 678c48a0 807d69b8 bb730aea f63925d1 efdbf800 fa9153ae 85767ef3 20fc735d 3cacc080 a719cd3a 26dc80f5 a928e757 c3effc72 8fc05585 f0fc3fc5 2b1fa015 bad9ff00 f5539738 e68118eb a1d88fbd b8fd4d58 5d234f5f f9640fd4 9357db71 c6da7e7d 6802aad9 59a1c2c2 83f0a9c4 71a9c2a8 1f414a79 39069dde 981034ac 09200c03 8a9ea331 a924e0f3 d7d2a4e7 d2988ac2 47076fde fceac738 e3ad205c 740053b0 7d680204 0c5cb3f5 1c7b7e15 372338a5 dbef46d1 48634280 49c0c9eb 5545a42a 3072471d 7daae6d1 e94b81e9 4c0aa2de 00dbb1cf 4eb5288e 307705e7 e9535140 11aa85e1 5719f4a5 f9bd29f4 9400c21b dab985d6 3519cb7d 9e14c2b6 dc93dff3 15d55708 12ed2eae 12d0aaed 909e719e a48c7140 9971af75 c66d8022 9c31c000 fddeddf9 f4aac93e b1382649 99304640 1ce09c67 0051e4de ac9b67b8 03e6230a 7072467d 3a543f63 86da2df2 cc4175dc 029c0240 e9efc9fe 7400e115 cc974d6f 7174d819 1c373f88 278154ef 608219e3 5594caac 01639f7f 5a98c5a4 ee669656 396e02f3 f2f6eb43 cba47458 c9006000 307f139e 79a008e6 874c4036 4993b9b3 8cf4e76f 38edc558 69b4b6b6 7586062f b07cd8e8 7dfd39fc ea1fb6da 0e12dc6d 0c1b181e 98c739a7 2cd3cd16 23b6c864 65ca8c67 a64f0074 c50048b7 b1418856 d8e55c9e 801c1cf1 dfd6aada ccd124d1 c317f0e5 b7139e0f d38ebf98 ab70dc6a 4e85a08d 51415624 f1d80079 3ed54228 6f3ed6f0 c5feb30d bb1cf1d4 faf5a00b b2de5fb5 c25b4ca9 112ca572 09c761eb 5215d4fc ad8d3808 1582f1c9 dbf867a5 44fa6df3 b094cbb9 800739c6 3279e7da 9ffd9919 6c5c4e73 bf072474 3ce793f9 d00442ce 4b503ccb 9daa0af0 9fed1fa8 f4a6bc36 6e3cc372 792df293 c8c1e327 9aad6b15 89f37ed4 e415e130 7af06ad7 97a2873b e463f313 f2e718ec 3a500536 16315cc4 c8c648f8 2f91f98a b8d3e981 cbc31b8d db81c0e3 90471cfb d30cba42 ae12262d 8ea7d7f3 a725fc48 fba08582 ac9b942f 1d46307a f5a403e2 bb2ab95b 669098d4 12c3a81d fa743497 2b7f78be 5adb98d7 7e7038e7 1ef8a725 ddda90f0 4272e857 9e738279 18c74ce2 a6136b8d 9da98ce3 a81df8ef 401414ea 2c0c7b72 026d208c e554ff00 8d68fd8b 56981324 a1007c9f ae392302 a885d50c ecaa4ab8
        $url = "http://imgsrc.baidu.com/forum/w=580/sign=db66b7dbd1ca7bcb7d7bc7278e086b3f/f5b11b90f603738d001f823ab51bb051f919ec86.jpg";
        $picData = file_get_contents($url);
        */
        $fileName = $customerId . $suffix;

//        $picPath = 'http://mimamori2p1hb.azurewebsites.net/upload/' . $hostCd . '_' . $fileName;
        $picPath = 'http://' . $_SERVER ['HTTP_HOST'] . '/upload/' . $hostCd . '_' . $fileName;
        updatePicInfo($conn, $customerId, $picPath, $updateDate, $code);
        if ($code == '200') {
//        $fileSize = file_put_contents($fileName, $picData);
            $fileSize = file_put_contents($fileName, base64_decode($picData));
            if (!$fileSize) {
                $code = '504';
                $sql = "UPDATE AZW008_custrelation SET picpath='',picupdatedate='' WHERE custid='$customerId'";
                $result = sqlsrv_query($conn, $sql);
                if (!$result) {
                    $code = '505';
                    $errors = sqlsrv_errors();
                }
            }
        }
    } else {
        $code = '501';
        $errors = array('paraments error.');
    }
} else {
    $code = '500';
    $errors = sqlsrv_errors();
}

sqlsrv_close($conn);

$arrReturn['code'] = $code;
$arrReturn['errors'] = $errors;
$arrReturn['picpath'] = $picPath;

sendResponse(json_encode($arrReturn));
