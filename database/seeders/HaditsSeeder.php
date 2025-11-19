<?php

namespace Database\Seeders;

use App\Models\Hadits;
use App\Models\HaditsSegment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HaditsSeeder extends Seeder
{
    public function run(): void
    {
        $arbain = [
            ['nomor' => 1, 'judul' => 'Setiap Amalan Tergantung pada Niat', 'tema' => 'Niat dan keikhlasan dalam beramal'],
            ['nomor' => 2, 'judul' => 'Iman, Islam, dan Ihsan (Hadits Jibril)', 'tema' => 'Pokok-pokok agama (rukun iman, islam, dan ihsan)'],
            ['nomor' => 3, 'judul' => 'Rukun Islam', 'tema' => 'Lima pilar utama dalam Islam'],
            ['nomor' => 4, 'judul' => 'Takdir (Empat Fase Penciptaan Manusia)', 'tema' => 'Proses penciptaan manusia dan takdir'],
            ['nomor' => 5, 'judul' => "Bahaya Bid'ah", 'tema' => 'Larangan mengada-adakan hal baru dalam agama'],
            ['nomor' => 6, 'judul' => 'Halal dan Haram Sudah Jelas', 'tema' => 'Menghindari syubhat (perkara yang tidak jelas)'],
            ['nomor' => 7, 'judul' => 'Nasihat (Agama adalah Nasihat)', 'tema' => 'Pentingnya nasihat dalam agama'],
            ['nomor' => 8, 'judul' => 'Perintah Memerangi Orang Kafir', 'tema' => 'Kewajiban berjihad dan batasan memerangi orang kafir'],
            ['nomor' => 9, 'judul' => 'Melaksanakan Perintah Sesuai Kemampuan', 'tema' => 'Kemudahan dalam syariat, menjalankan kewajiban sesuai kemampuan'],
            ['nomor' => 10, 'judul' => 'Memakan Harta Haram', 'tema' => 'Pentingnya mencari rezeki yang halal dan doa yang mustajab'],
            ['nomor' => 11, 'judul' => "Tinggalkan Keraguan (Wara')", 'tema' => "Sikap wara' (hati-hati) dalam beragama"],
            ['nomor' => 12, 'judul' => 'Meninggalkan yang Tidak Bermanfaat', 'tema' => 'Tanda kebaikan Islam seseorang'],
            ['nomor' => 13, 'judul' => 'Mencintai Kebaikan untuk Orang Lain', 'tema' => 'Persaudaraan dan etika sesama muslim'],
            ['nomor' => 14, 'judul' => "Larangan Menumpahkan Darah Muslim", 'tema' => 'Kehormatan darah seorang muslim'],
            ['nomor' => 15, 'judul' => 'Adab Bertamu dan Berbicara Baik', 'tema' => 'Etika terhadap tetangga, tamu, dan menjaga lisan'],
            ['nomor' => 16, 'judul' => 'Larangan Marah', 'tema' => 'Mengendalikan emosi dan amarah'],
            ['nomor' => 17, 'judul' => 'Ihsan dan Berbuat Baik dalam Segala Hal', 'tema' => 'Berbuat baik kepada makhluk (termasuk hewan)'],
            ['nomor' => 18, 'judul' => 'Takwa dan Akhlak Mulia', 'tema' => 'Berbuat baik setelah melakukan kesalahan dan berakhlak mulia'],
            ['nomor' => 19, 'judul' => 'Meminta Pertolongan dan Perlindungan Allah', 'tema' => 'Tawakal, keimanan, dan ketetapan takdir'],
            ['nomor' => 20, 'judul' => 'Pentingnya Memiliki Rasa Malu', 'tema' => 'Rasa malu adalah bagian dari iman'],
            ['nomor' => 21, 'judul' => 'Istiqamah (Berpegang Teguh pada Islam)', 'tema' => 'Perintah untuk istiqamah di jalan Allah'],
            ['nomor' => 22, 'judul' => 'Jalan Menuju Surga (Menjalankan Kewajiban)', 'tema' => 'Amalan yang memasukkan ke surga dan menjauhkan dari neraka'],
            ['nomor' => 23, 'judul' => 'Bersuci (Kesucian adalah Setengah Iman)', 'tema' => 'Pentingnya bersuci (thaharah) dan ibadah'],
            ['nomor' => 24, 'judul' => 'Larangan Berbuat Zalim', 'tema' => 'Menjauhi kezaliman dan pentingnya syukur'],
            ['nomor' => 25, 'judul' => 'Amalan Sunnah (Sedekah dengan Harta dan Non-Harta)', 'tema' => 'Banyak jalan untuk beramal kebajikan dan sedekah'],
            ['nomor' => 26, 'judul' => 'Mendamaikan Dua Pihak', 'tema' => 'Pentingnya mendamaikan perselisihan dan sedekah'],
            ['nomor' => 27, 'judul' => 'Kebaikan adalah Akhlak Mulia', 'tema' => 'Definisi kebaikan (al-birr) dan dosa (al-ithm)'],
            ['nomor' => 28, 'judul' => 'Berpegang Teguh pada Sunnah', 'tema' => 'Wajib mengikuti sunnah Rasulullah SAW dan Khulafaur Rasyidin'],
            ['nomor' => 29, 'judul' => 'Jalan Menuju Surga (Perbuatan Baik)', 'tema' => 'Amalan yang memasukkan ke surga dan menjauhkan dari neraka'],
            ['nomor' => 30, 'judul' => 'Menunaikan Hak Allah (Rukun Islam)', 'tema' => 'Rukun Islam dan batasan-batasan agama'],
            ['nomor' => 31, 'judul' => 'Zuhud di Dunia', 'tema' => 'Menjadi zuhud dan mempersiapkan diri untuk akhirat'],
            ['nomor' => 32, 'judul' => 'Larangan Berbuat Kerusakan', 'tema' => 'Tidak boleh membahayakan diri sendiri atau orang lain'],
            ['nomor' => 33, 'judul' => 'Hakim Berhukum dengan Adil', 'tema' => 'Tata cara peradilan dan keadilan seorang hakim'],
            ['nomor' => 34, 'judul' => 'Kewajiban Mengubah Kemungkaran', 'tema' => 'Amar ma’ruf nahi munkar (mengajak kebaikan dan mencegah kemungkaran)'],
            ['nomor' => 35, 'judul' => 'Hak Sesama Muslim', 'tema' => 'Enam kewajiban muslim atas muslim lainnya'],
            ['nomor' => 36, 'judul' => 'Menutupi Aib Sesama Muslim', 'tema' => 'Tolong menolong dan menutupi aib sesama muslim'],
            ['nomor' => 37, 'judul' => 'Kebaikan Berlipat Ganda', 'tema' => 'Besarnya pahala amal baik dan balasan amal buruk yang setimpal'],
            ['nomor' => 38, 'judul' => 'Mendekatkan Diri kepada Allah (Amalan Fardhu dan Sunnah)', 'tema' => 'Mencapai kedekatan dengan Allah melalui ibadah wajib dan sunnah'],
            ['nomor' => 39, 'judul' => 'Diampuni Dosa yang Tidak Disengaja', 'tema' => 'Pemaafan Allah atas kesalahan, kelupaan, dan keterpaksaan'],
            ['nomor' => 40, 'judul' => 'Hidup di Dunia bagai Orang Asing', 'tema' => 'Persiapan menghadapi kematian dan kehidupan akhirat'],
            ['nomor' => 41, 'judul' => 'Mengikuti Syariat Nabi', 'tema' => 'Keimanan yang benar dan mengikuti ajaran Rasulullah SAW'],
            ['nomor' => 42, 'judul' => 'Luasnya Ampunan Allah', 'tema' => 'Harapan akan ampunan Allah SWT bagi hamba-Nya'],
        ];

        $umdatulAhkamCsv = <<<'CSV'
43,Innamal a'mālu bin-niyyāt,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Niat,1,1
44,Niat tempatnya di hati (tidak perlu diucapkan),Umdatul Ahkam,Kitab Ath-Thaharah - Bab Niat,2,2
45,Adab masuk tempat buang air,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Adab Khala',3,3
46,Larangan buang air di jalan atau tempat berteduh,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Adab Khala',4,4
47,Larangan buang air sambil berdiri,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Adab Khala',5,5
48,Keutamaan bersiwak,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Siwak,6,6
49,Wudhu sempurna (membasuh anggota tiga kali),Umdatul Ahkam,Kitab Ath-Thaharah - Bab Wudhu,7,7
50,Tata cara wudhu Nabi ﷺ,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Wudhu,8,8
51,Cara mandi junub Nabi ﷺ,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Ghusl,9,9
52,Wajibnya mandi karena junub,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Ghusl,10,10
53,Mandi junub yang sempurna,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Ghusl,11,11
54,Tayammum menggantikan wudhu dan mandi,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Tayammum,12,12
55,Hukum madzi dan wadi,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Najis,13,13
56,Wanita haid tidak boleh shalat,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Haid,14,14
57,Larangan jimak dengan wanita haid,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Haid,15,15
58,Wanita haid tidak wajib qadha shalat,Umdatul Ahkam,Kitab Ath-Thaharah - Bab Haid,16,16
59,Waktu shalat Subuh,Umdatul Ahkam,Kitab Ash-Shalah - Bab Waktu Shalat,17,17
60,Waktu shalat Zhuhur,Umdatul Ahkam,Kitab Ash-Shalah - Bab Waktu Shalat,18,18
61,Waktu shalat Ashar,Umdatul Ahkam,Kitab Ash-Shalah - Bab Waktu Shalat,19,19
62,Waktu shalat Maghrib,Umdatul Ahkam,Kitab Ash-Shalah - Bab Waktu Shalat,20,20
63,Waktu shalat Isya,Umdatul Ahkam,Kitab Ash-Shalah - Bab Waktu Shalat,21,21
64,Mengakhirkan shalat Isya,Umdatul Ahkam,Kitab Ash-Shalah - Bab Waktu Shalat,22,22
65,Larangan shalat setelah Ashar dan Subuh,Umdatul Ahkam,Kitab Ash-Shalah - Bab Waktu Shalat,23,23
66,Shalat malam di bulan Ramadhan (tarawih),Umdatul Ahkam,Kitab Ash-Shalah - Bab Qiyam Ramadhan,24,24
67,Keutamaan shalat malam,Umdatul Ahkam,Kitab Ash-Shalah - Bab Tahajjud,25,25
68,Shalat witir satu rakaat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Witir,26,26
69,Shalat witir tiga rakaat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Witir,27,27
70,Shalat witir lima rakaat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Witir,28,28
71,Keutamaan shalat berjamaah,Umdatul Ahkam,Kitab Ash-Shalah - Bab Shalat Berjamaah,29,29
72,Shalat berjamaah lebih utama 27 derajat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Shalat Berjamaah,30,30
73,Shalat berjamaah lebih utama 25 derajat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Shalat Berjamaah,31,31
74,Shalat berjamaah 27 derajat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Shalat Berjamaah,32,32
75,Shalat Isya berjamaah seperti separuh malam,Umdatul Ahkam,Kitab Ash-Shalah - Bab Shalat Berjamaah,33,33
id,judul,kitab,bab,nomor,urutan
76,Adzan dan iqamah,Umdatul Ahkam,Kitab Ash-Shalah - Bab Adzan,34,34
77,Keutamaan adzan,Umdatul Ahkam,Kitab Ash-Shalah - Bab Adzan,35,35
78,Lafaz adzan,Umdatul Ahkam,Kitab Ash-Shalah - Bab Adzan,36,36
79,Tartib (urutan) adzan,Umdatul Ahkam,Kitab Ash-Shalah - Bab Adzan,37,37
80,Menjawab adzan,Umdatul Ahkam,Kitab Ash-Shalah - Bab Adzan,38,38
81,Doa setelah adzan,Umdatul Ahkam,Kitab Ash-Shalah - Bab Adzan,39,39
82,Doa antara adzan dan iqamah,Umdatul Ahkam,Kitab Ash-Shalah - Bab Adzan,40,40
83,Iqamah,Umdatul Ahkam,Kitab Ash-Shalah - Bab Iqamah,41,41
84,Menghadap kiblat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Kiblat,42,42
85,Sutrah (penghalang) bagi orang shalat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sutrah,43,43
86,Larangan lewat di depan orang shalat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sutrah,44,44
87,Meluruskan dan merapatkan shaf,Umdatul Ahkam,Kitab Ash-Shalah - Bab Shaf,45,45
88,Merapatkan shaf dan meluruskan,Umdatul Ahkam,Kitab Ash-Shalah - Bab Shaf,46,46
89,Shaf laki-laki, anak-anak, lalu wanita,Umdatul Ahkam,Kitab Ash-Shalah - Bab Shaf,47,47
90,Keutamaan shaf pertama,Umdatul Ahkam,Kitab Ash-Shalah - Bab Shaf,48,48
91,Keutamaan berdiri di sebelah kanan imam,Umdatul Ahkam,Kitab Ash-Shalah - Bab Makmum,49,49
92,Makmum mengikuti imam,Umdatul Ahkam,Kitab Ash-Shalah - Bab Makmum,50,50
93,Larangan mendahului imam,Umdatul Ahkam,Kitab Ash-Shalah - Bab Makmum,51,51
94,Imam adalah penjamin,Umdatul Ahkam,Kitab Ash-Shalah - Bab Imamah,52,52
95,Orang yang paling berhak jadi imam,Umdatul Ahkam,Kitab Ash-Shalah - Bab Imamah,53,53
96,Shalatnya orang di belakang imam,Umdatul Ahkam,Kitab Ash-Shalah - Bab Makmum,54,54
97,Shalat orang yang ketinggalan rakaat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Masbuq,55,55
98,Shalat orang yang ketinggalan beberapa rakaat,Umdatul Ahkam,Kitab Ash-Shalah - Bab Masbuq,56,56
99,Takbiratul ihram,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,57,57
100,Meletakkan tangan kanan di atas kiri,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,58,58
101,Doa istiftah,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,59,59
102,Ta'awwudz dan basmalah,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,60,60
103,Bacaan ketika ruku',Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,61,61
104,Thuma'ninah dalam ruku',Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,62,62
105,I'tidal setelah ruku',Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,63,63
106,Bacaan ketika sujud,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,64,64
107,Thuma'ninah dalam sujud,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,65,65
108,Duduk di antara dua sujud,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,66,66
109,Duduk istirahah,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,67,67
id,judul,kitab,bab,nomor,urutan
110,Duduk iftirasy pada tasyahhud awal,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,68,68
111,Duduk tawarruk pada tasyahhud akhir,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,69,69
112,Mengangkat tangan ketika takbir,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,70,70
113,Tangan sejajar dengan bahu ketika takbir,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,71,71
114,Tangan sejajar dengan telinga ketika takbir,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,72,72
115,Meletakkan tangan di paha ketika duduk,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,73,73
116,Mengacungkan jari telunjuk ketika tasyahhud,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,74,74
117,Tasyahhud awal,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,75,75
118,Shalawat Ibrahimiyah,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,76,76
119,Doa setelah tasyahhud akhir,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,77,77
120,Salam ke kanan dan ke kiri,Umdatul Ahkam,Kitab Ash-Shalah - Bab Sifat Shalat Nabi ﷺ,78,78
121,Dzikir setelah salam,Umdatul Ahkam,Kitab Ash-Shalah - Bab Dzikir Setelah Shalat,79,79
122,Shalat sunnah rawatib,Umdatul Ahkam,Kitab Ash-Shalah - Bab Rawatib,80,80
123,Shalat witir,Umdatul Ahkam,Kitab Ash-Shalah - Bab Witir,81,81
124,Shalat dhuha,Umdatul Ahkam,Kitab Ash-Shalah - Bab Dhuha,82,82
125,Shalat gerhana matahari,Umdatul Ahkam,Kitab Ash-Shalah - Bab Kusuf,83,83
126,Shalat gerhana bulan,Umdatul Ahkam,Kitab Ash-Shalah - Bab Khusuf,84,84
127,Shalat istisqa (minta hujan),Umdatul Ahkam,Kitab Ash-Shalah - Bab Istisqa,85,85
128,Shalat jenazah,Umdatul Ahkam,Kitab Al-Janaiz - Bab Shalat Jenazah,86,86
129,Takbir shalat jenazah empat kali,Umdatul Ahkam,Kitab Al-Janaiz - Bab Shalat Jenazah,87,87
130,Membaca Al-Fatihah pada shalat jenazah,Umdatul Ahkam,Kitab Al-Janaiz - Bab Shalat Jenazah,88,88
131,Shalawat atas Nabi pada shalat jenazah,Umdatul Ahkam,Kitab Al-Janaiz - Bab Shalat Jenazah,89,89
132,Doa untuk mayit pada shalat jenazah,Umdatul Ahkam,Kitab Al-Janaiz - Bab Shalat Jenazah,90,90
133,Salam pada shalat jenazah,Umdatul Ahkam,Kitab Al-Janaiz - Bab Shalat Jenazah,91,91
134,Keutamaan puasa Ramadhan,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Ramadhan,92,92
135,Niat puasa Ramadhan,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Niat Puasa,93,93
136,Sahur,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Sahur,94,94
137,Menyegerakan berbuka,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Iftar,95,95
138,Puasa sunnah Senin-Kamis,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Puasa Sunnah,96,96
139,Puasa Arafah,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Puasa Arafah,97,97
140,Puasa Asyura,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Puasa Asyura,98,98
141,Larangan puasa hari raya,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Larangan Puasa,99,99
142,Larangan puasa wishal,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Wishal,100,100
143,Puasa sunnah di jalan Allah,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Puasa Sunnah,101,101
144,Lailatul Qadar di sepuluh malam terakhir,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Lailatul Qadar,102,102
id,judul,kitab,bab,nomor,urutan
145,Lailatul Qadar pada malam ganjil,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Lailatul Qadar,103,103
146,Keutamaan sedekah di bulan Ramadhan,Umdatul Ahkam,Kitab Az-Zakat - Bab Sedekah,104,104
147,Zakat fitrah,Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Fithri,105,105
148,Waktu mengeluarkan zakat fitrah,Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Fithri,106,106
149,Zakat mal (harta),Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Mal,107,107
150,Nishab emas dan perak,Umdatul Ahkam,Kitab Az-Zakat - Bab Nishab,108,108
151,Zakat ternak,Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Ternak,109,109
152,Zakat pertanian,Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Zuru',110,110
153,Miqat haji dan umrah,Umdatul Ahkam,Kitab Al-Hajj - Bab Miqat,111,111
154,Talbiyah,Umdatul Ahkam,Kitab Al-Hajj - Bab Talbiyah,112,112
155,Thawaf qudum,Umdatul Ahkam,Kitab Al-Hajj - Bab Thawaf,113,113
156,Sa'i antara Shafa dan Marwah,Umdatul Ahkam,Kitab Al-Hajj - Bab Sa'i,114,114
157,Wukuf di Arafah,Umdatul Ahkam,Kitab Al-Hajj - Bab Arafah,115,115
158,Mabit di Muzdalifah,Umdatul Ahkam,Kitab Al-Hajj - Bab Muzdalifah,116,116
159,Melempar jumrah Aqabah,Umdatul Ahkam,Kitab Al-Hajj - Bab Jumrah,117,117
160,Thawaf ifadah,Umdatul Ahkam,Kitab Al-Hajj - Bab Ifadhah,118,118
161,Tahallul setelah thawaf ifadah,Umdatul Ahkam,Kitab Al-Hajj - Bab Tahallul,119,119
162,Hewan kurban,Umdatul Ahkam,Kitab Al-Udhhiyah - Bab Kurban,120,120
163,Larangan mengambil upah tukang jagal,Umdatul Ahkam,Kitab Al-Udhhiyah - Bab Kurban,121,121
164,Jual beli yang sah,Umdatul Ahkam,Kitab Al-Buyu' - Bab Jual Beli,122,122
165,Larangan riba,Umdatul Ahkam,Kitab Al-Buyu' - Bab Riba,123,123
166,Larangan jual beli gharar,Umdatul Ahkam,Kitab Al-Buyu' - Bab Gharar,124,124
167,Khiyar majlis,Umdatul Ahkam,Kitab Al-Buyu' - Bab Khiyar,125,125
168,Larangan ihtikar (menimbun barang),Umdatul Ahkam,Kitab Al-Buyu' - Bab Ihtikar,126,126
169,Pernikahan yang sah,Umdatul Ahkam,Kitab An-Nikah - Bab Nikah,127,127
170,Wali nikah,Umdatul Ahkam,Kitab An-Nikah - Bab Wali,128,128
171,Mahar,Umdatul Ahkam,Kitab An-Nikah - Bab Mahar,129,129
172,Walimah,Umdatul Ahkam,Kitab An-Nikah - Bab Walimah,130,130
173,Birul walidain (berbakti kepada orang tua),Umdatul Ahkam,Kitab Al-Adab - Bab Birrul Walidain,131,131
174,Larangan durhaka kepada orang tua,Umdatul Ahkam,Kitab Al-Adab - Bab Birrul Walidain,132,132
175,Keutamaan jihad,Umdatul Ahkam,Kitab Al-Jihad - Bab Jihad,133,133
176,Keutamaan mati syahid,Umdatul Ahkam,Kitab Al-Jihad - Bab Syahid,134,134
177,Larangan membunuh diri sendiri,Umdatul Ahkam,Kitab Al-Hudud - Bab Bunuh Diri,135,135
178,Hukuman zina,Umdatul Ahkam,Kitab Al-Hudud - Bab Zina,136,136
179,Hukuman minum khamar,Umdatul Ahkam,Kitab Al-Hudud - Bab Khamar,137,137
id,judul,kitab,bab,nomor,urutan
180,Hukuman qadzaf (menuduh zina),Umdatul Ahkam,Kitab Al-Hudud - Bab Qadzaf,138,138
181,Hukuman pencurian,Umdatul Ahkam,Kitab Al-Hudud - Bab Sariqah,139,139
182,Qishash jiwa,Umdatul Ahkam,Kitab Al-Qishash - Bab Qishash,140,140
183,Diyat pembunuhan,Umdatul Ahkam,Kitab Ad-Diyat - Bab Diyat,141,141
184,Puasa sunnah di jalan Allah,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Puasa Sunnah,142,142
185,Lailatul Qadar di sepuluh malam terakhir,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Lailatul Qadar,143,143
186,Lailatul Qadar pada malam ganjil,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Lailatul Qadar,144,144
187,Keutamaan sedekah,Umdatul Ahkam,Kitab Az-Zakat - Bab Sedekah,145,145
188,Larangan meminta-minta,Umdatul Ahkam,Kitab Az-Zakat - Bab Larangan Meminta,146,146
189,Haji mabrur,Umdatul Ahkam,Kitab Al-Hajj - Bab Keutamaan Haji,147,147
190,Larangan mencaci orang tua,Umdatul Ahkam,Kitab Al-Adab - Bab Birrul Walidain,148,148
191,Birul walidain setelah keduanya wafat,Umdatul Ahkam,Kitab Al-Adab - Bab Birrul Walidain,149,149
192,Keutamaan jihad,Umdatul Ahkam,Kitab Al-Jihad - Bab Jihad,150,150
193,Keutamaan mati syahid,Umdatul Ahkam,Kitab Al-Jihad - Bab Syahid,151,151
194,Larangan membunuh diri sendiri,Umdatul Ahkam,Kitab Al-Hudud - Bab Bunuh Diri,152,152
195,Hukuman zina,Umdatul Ahkam,Kitab Al-Hudud - Bab Zina,153,153
196,Hukuman minum khamar,Umdatul Ahkam,Kitab Al-Hudud - Bab Khamar,154,154
197,Hukuman qadzaf,Umdatul Ahkam,Kitab Al-Hudud - Bab Qadzaf,155,155
198,Hukuman pencurian,Umdatul Ahkam,Kitab Al-Hudud - Bab Sariqah,156,156
199,Qishash jiwa,Umdatul Ahkam,Kitab Al-Qishash - Bab Qishash,157,157
200,Diyat pembunuhan,Umdatul Ahkam,Kitab Ad-Diyat - Bab Diyat,158,158
201,Puasa sunnah di jalan Allah,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Puasa Sunnah,159,159
202,Lailatul Qadar di sepuluh malam terakhir,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Lailatul Qadar,160,160
203,Lailatul Qadar pada malam ganjil,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Lailatul Qadar,161,161
204,Keutamaan sedekah di bulan Ramadhan,Umdatul Ahkam,Kitab Az-Zakat - Bab Sedekah,162,162
205,Zakat fitrah,Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Fithri,163,163
206,Waktu mengeluarkan zakat fitrah,Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Fithri,164,164
207,Zakat mal (harta),Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Mal,165,165
208,Nishab emas dan perak,Umdatul Ahkam,Kitab Az-Zakat - Bab Nishab,166,166
209,Zakat ternak,Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Ternak,167,167
210,Zakat pertanian,Umdatul Ahkam,Kitab Az-Zakat - Bab Zakat Zuru',168,168
211,Miqat haji dan umrah,Umdatul Ahkam,Kitab Al-Hajj - Bab Miqat,169,169
212,Talbiyah,Umdatul Ahkam,Kitab Al-Hajj - Bab Talbiyah,170,170
213,Thawaf qudum,Umdatul Ahkam,Kitab Al-Hajj - Bab Thawaf,171,171
214,Sa'i antara Shafa dan Marwah,Umdatul Ahkam,Kitab Al-Hajj - Bab Sa'i,172,172
215,Wukuf di Arafah,Umdatul Ahkam,Kitab Al-Hajj - Bab Arafah,173,173
id,judul,kitab,bab,nomor,urutan
216,Mabit di Muzdalifah,Umdatul Ahkam,Kitab Al-Hajj - Bab Muzdalifah,174,174
217,Melempar jumrah Aqabah,Umdatul Ahkam,Kitab Al-Hajj - Bab Jumrah,175,175
218,Melempar jumrah tiga hari tasyriq,Umdatul Ahkam,Kitab Al-Hajj - Bab Jumrah,176,176
219,Thawaf ifadah,Umdatul Ahkam,Kitab Al-Hajj - Bab Ifadhah,177,177
220,Tahallul setelah thawaf ifadah,Umdatul Ahkam,Kitab Al-Hajj - Bab Tahallul,178,178
221,Thawaf wada',Umdatul Ahkam,Kitab Al-Hajj - Bab Wada',179,179
222,Hewan kurban,Umdatul Ahkam,Kitab Al-Udhhiyah - Bab Kurban,180,180
223,Larangan mengambil upah tukang jagal,Umdatul Ahkam,Kitab Al-Udhhiyah - Bab Kurban,181,181
224,Jual beli yang sah,Umdatul Ahkam,Kitab Al-Buyu' - Bab Jual Beli,182,182
225,Larangan riba,Umdatul Ahkam,Kitab Al-Buyu' - Bab Riba,183,183
226,Larangan jual beli gharar,Umdatul Ahkam,Kitab Al-Buyu' - Bab Gharar,184,184
227,Khiyar majlis,Umdatul Ahkam,Kitab Al-Buyu' - Bab Khiyar,185,185
228,Larangan ihtikar (menimbun barang),Umdatul Ahkam,Kitab Al-Buyu' - Bab Ihtikar,186,186
229,Pernikahan yang sah,Umdatul Ahkam,Kitab An-Nikah - Bab Nikah,187,187
230,Wali nikah,Umdatul Ahkam,Kitab An-Nikah - Bab Wali,188,188
231,Mahar,Umdatul Ahkam,Kitab An-Nikah - Bab Mahar,189,189
232,Walimah,Umdatul Ahkam,Kitab An-Nikah - Bab Walimah,190,190
233,Larangan thalak tiga sekaligus,Umdatul Ahkam,Kitab Ath-Thalaq - Bab Thalaq,191,191
234,Iddah thalak,Umdatul Ahkam,Kitab Ath-Thalaq - Bab Iddah,192,192
235,Khulu',Umdatul Ahkam,Kitab Ath-Thalaq - Bab Khulu',193,193
236,Larangan menggauli budak milik orang lain,Umdatul Ahkam,Kitab Al-Hudud - Bab Zina,194,194
237,Hukuman zina,Umdatul Ahkam,Kitab Al-Hudud - Bab Zina,195,195
238,Hukuman minum khamar,Umdatul Ahkam,Kitab Al-Hudud - Bab Khamar,196,196
239,Hukuman qadzaf,Umdatul Ahkam,Kitab Al-Hudud - Bab Qadzaf,197,197
240,Hukuman pencurian,Umdatul Ahkam,Kitab Al-Hudud - Bab Sariqah,198,198
241,Qishash jiwa,Umdatul Ahkam,Kitab Al-Qishash - Bab Qishash,199,199
242,Diyat pembunuhan,Umdatul Ahkam,Kitab Ad-Diyat - Bab Diyat,200,200
243,Puasa sunnah di jalan Allah,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Puasa Sunnah,201,201
244,Lailatul Qadar di sepuluh malam terakhir,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Lailatul Qadar,202,202
245,Lailatul Qadar pada malam ganjil,Umdatul Ahkam,Kitab Ash-Shiyam - Bab Lailatul Qadar,203,203
246,Doa ketika melihat hilal,Umdatul Ahkam,Kitab Ad-Du'a - Bab Doa Hilal,204,204
247,Keutamaan ilmu,Umdatul Ahkam,Kitab Al-Ilmu - Bab Fadhilatul Ilmi,205,205
248,Penutup kitab – Doa sapu jagat,Umdatul Ahkam,Kitab Ad-Du'a - Bab Doa Umum,206,206
CSV;

        $umdatulAhkam = [];
        foreach (preg_split("/\r\n|\n|\r/", trim($umdatulAhkamCsv)) as $line) {
            if ($line === '' || str_starts_with($line, 'id,')) {
                continue;
            }

            $columns = str_getcsv($line);
            if (count($columns) < 6) {
                continue;
            }

            $umdatulAhkam[] = [
                'judul' => trim($columns[1]),
                'kitab' => trim($columns[2]),
                'bab' => trim($columns[3]),
                'nomor' => (int) $columns[4],
                'urutan' => (int) $columns[5],
            ];
        }

        $haditsData = array_merge(
            array_map(fn ($data) => [
                'judul' => $data['judul'],
                'kitab' => 'Arbain Nawawi',
                'bab' => $data['tema'],
                'nomor' => $data['nomor'],
                'urutan' => $data['nomor'],
            ], $arbain),
            $umdatulAhkam
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('hadits_setoran_details')->truncate();
        DB::table('hadits_setorans')->truncate();
        DB::table('hadits_targets')->truncate();
        DB::table('hadits_segments')->truncate();
        DB::table('hadits')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($haditsData as $data) {
            $hadits = Hadits::create($data);

            HaditsSegment::create([
                'hadits_id' => $hadits->id,
                'urutan' => 1,
                'teks' => 'Segment 1'
            ]);
        }
    }
}
