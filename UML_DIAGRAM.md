# UML Diagram SI-PENA

Semua diagram menggunakan format PlantUML. Render di https://www.plantuml.com/plantuml/uml atau plugin PlantUML di VSCode.

---

## 1. Use Case Diagram

```plantuml
@startuml UseCase
left to right direction
skinparam packageStyle rectangle

actor "Ormawa/Mahasiswa" as Ormawa
actor "Kemahasiswaan" as Kem
actor "Kaur Kemahasiswaan" as KaurKem
actor "Keuangan" as Keu
actor "Kaur Keuangan" as KaurKeu
actor "WD2" as WD2

rectangle "SI-PENA" {
    usecase "Login" as UC1
    usecase "Register" as UC2
    usecase "Mengajukan Dana" as UC3
    usecase "Melihat Pengajuan" as UC4
    usecase "Melihat Detail Pengajuan" as UC5
    usecase "Mengubah Status" as UC6
    usecase "Memberikan Revisi" as UC7
    usecase "Mengisi Dana Disetujui" as UC8
    usecase "Meneruskan ke Kaur Kemahasiswaan" as UC9
    usecase "Meneruskan ke Keuangan" as UC10
    usecase "Meneruskan ke Kaur Keuangan" as UC11
    usecase "Meneruskan ke WD2" as UC12
    usecase "Approve / Tolak" as UC13
    usecase "Melihat Grafik" as UC14
    usecase "Melihat Report" as UC15
    usecase "Download Report" as UC16
    usecase "Melihat Dashboard" as UC17
}

Ormawa --> UC1
Ormawa --> UC2
Ormawa --> UC3
Ormawa --> UC4
Ormawa --> UC5
Ormawa --> UC14
Ormawa --> UC17

Kem --> UC1
Kem --> UC4
Kem --> UC5
Kem --> UC6
Kem --> UC7
Kem --> UC9
Kem --> UC14
Kem --> UC15
Kem --> UC16
Kem --> UC17

KaurKem --> UC1
KaurKem --> UC4
KaurKem --> UC5
KaurKem --> UC6
KaurKem --> UC7
KaurKem --> UC10
KaurKem --> UC14
KaurKem --> UC15
KaurKem --> UC16
KaurKem --> UC17

Keu --> UC1
Keu --> UC4
Keu --> UC5
Keu --> UC6
Keu --> UC7
Keu --> UC11
Keu --> UC14
Keu --> UC15
Keu --> UC16
Keu --> UC17

KaurKeu --> UC1
KaurKeu --> UC4
KaurKeu --> UC5
KaurKeu --> UC6
KaurKeu --> UC7
KaurKeu --> UC8
KaurKeu --> UC12
KaurKeu --> UC14
KaurKeu --> UC15
KaurKeu --> UC16
KaurKeu --> UC17

WD2 --> UC1
WD2 --> UC4
WD2 --> UC5
WD2 --> UC13
WD2 --> UC14
WD2 --> UC15
WD2 --> UC16
WD2 --> UC17
@enduml
```

---

## 2. Activity Diagram (Alur Pengajuan Dana)

```plantuml
@startuml Activity
start

:Ormawa mengajukan dana\n(isi form + upload proposal);
:Status = "Belum Diproses";

:Kemahasiswaan review pengajuan;

if (Keputusan Kemahasiswaan?) then (Proses)
    :Status = "Sedang Diproses Kemahasiswaan";
    :Status = "Diteruskan ke Kaur Kemahasiswaan";
else (Revisi/Tolak)
    if (Revisi?) then (ya)
        :Status = "Revisi";
        :Kembali ke Ormawa;
        stop
    else (Tolak)
        :Status = "Ditolak";
        stop
    endif
endif

:Kaur Kemahasiswaan review;

if (Keputusan Kaur Kemahasiswaan?) then (Forward)
    :Status = "Diteruskan ke Keuangan";
else (Revisi/Tolak)
    if (Revisi?) then (ya)
        :Status = "Revisi";
        stop
    else (Tolak)
        :Status = "Ditolak";
        stop
    endif
endif

:Keuangan review pengajuan;

if (Keputusan Keuangan?) then (Proses)
    :Status = "Sedang Diproses Keuangan";
    :Status = "Diteruskan ke Kaur Keuangan";
else (Revisi/Tolak)
    if (Revisi?) then (ya)
        :Status = "Revisi";
        stop
    else (Tolak)
        :Status = "Ditolak";
        stop
    endif
endif

:Kaur Keuangan mengisi\nnominal dana disetujui;

if (Keputusan Kaur Keuangan?) then (Forward)
    :Status = "Menunggu Persetujuan WD2";
else (Revisi/Tolak)
    if (Revisi?) then (ya)
        :Status = "Revisi";
        stop
    else (Tolak)
        :Status = "Ditolak";
        stop
    endif
endif

:WD2 review pengajuan;

if (Keputusan WD2?) then (Setuju)
    :Status = "Disetujui";
    :dana_disetujui = dana_disetujui_keuangan;
    :Data masuk Grafik & Report;
else (Tolak)
    :Status = "Ditolak";
endif

stop
@enduml
```

---

## 3. Class Diagram (ERD)

```plantuml
@startuml Class
skinparam classAttributeIconSize 0

class User {
    +id: bigint <<PK>>
    +name: string
    +username: string <<unique>>
    +email: string [nullable]
    +password: string
    +role: enum
    +organization_name: string [nullable]
    +created_at: timestamp
    +updated_at: timestamp
    --
    role: mahasiswa | ormawa | kemahasiswaan |
          keuangan | kaur_kemahasiswaan |
          kaur_keuangan | wd2
}

class FundRequest {
    +id: bigint <<PK>>
    +user_id: bigint <<FK>>
    +tahun_ajaran: string
    +tanggal_mulai: date
    +tanggal_selesai: date
    +jenis_kegiatan: string
    +tingkat_kegiatan: string
    +nama_kegiatan: string
    +deskripsi: text
    +proposal_file: string
    +dana_diajukan: decimal(15,2)
    +dana_disetujui_kemahasiswaan: decimal(15,2) [nullable]
    +dana_disetujui_keuangan: decimal(15,2) [nullable]
    +dana_disetujui: decimal(15,2) [nullable]
    +status: string
    +created_at: timestamp
    +updated_at: timestamp
}

class Revision {
    +id: bigint <<PK>>
    +fund_request_id: bigint <<FK>>
    +user_id: bigint <<FK>>
    +catatan: text
    +created_at: timestamp
    +updated_at: timestamp
}

class FundBudget {
    +id: bigint <<PK>>
    +kategori: enum(ormawa, lomba)
    +nama_unit: string
    +triwulan: integer
    +total_dana: decimal(15,2)
    +sisa_dana: decimal(15,2)
    +created_at: timestamp
    +updated_at: timestamp
}

User "1" --> "0..*" FundRequest : mengajukan
User "1" --> "0..*" Revision : memberi revisi
FundRequest "1" --> "0..*" Revision : memiliki
@enduml
```

---

## 4. Sequence Diagram (Pengajuan Dana Berhasil)

```plantuml
@startuml Sequence
actor Ormawa
participant "Kemahasiswaan" as Kem
participant "Kaur\nKemahasiswaan" as KaurKem
participant "Keuangan" as Keu
participant "Kaur\nKeuangan" as KaurKeu
participant "WD2" as WD2
database "Database" as DB

Ormawa -> DB : Ajukan dana\n(form + proposal)
DB --> Ormawa : Status: Belum Diproses

== Review Kemahasiswaan ==
Kem -> DB : Ubah status
DB --> Kem : Status: Sedang Diproses Kemahasiswaan
Kem -> DB : Teruskan
DB --> Kem : Status: Diteruskan ke Kaur Kemahasiswaan

== Persetujuan Kaur Kemahasiswaan ==
KaurKem -> DB : Teruskan ke Keuangan
DB --> KaurKem : Status: Diteruskan ke Keuangan

== Review Keuangan ==
Keu -> DB : Ubah status
DB --> Keu : Status: Sedang Diproses Keuangan
Keu -> DB : Teruskan
DB --> Keu : Status: Diteruskan ke Kaur Keuangan

== Persetujuan Kaur Keuangan ==
KaurKeu -> DB : Isi dana disetujui
DB --> KaurKeu : dana_disetujui_keuangan tersimpan
KaurKeu -> DB : Teruskan ke WD2
DB --> KaurKeu : Status: Menunggu Persetujuan WD2

== Persetujuan WD2 ==
WD2 -> DB : Approve
DB --> WD2 : Status: Disetujui\n(dana_disetujui = dana_disetujui_keuangan)

== Hasil ==
Ormawa -> DB : Lihat status
DB --> Ormawa : Disetujui ✓
note right : Data muncul di\nGrafik & Report
@enduml
```

---

## 5. State Diagram (Status Pengajuan)

```plantuml
@startuml State
[*] --> BelumDiproses

BelumDiproses --> SedangDiprosesKemahasiswaan : Kemahasiswaan review
BelumDiproses --> Revisi : Kemahasiswaan
BelumDiproses --> Ditolak : Kemahasiswaan

SedangDiprosesKemahasiswaan --> DiteruskanKeKaurKemahasiswaan : Kemahasiswaan forward
SedangDiprosesKemahasiswaan --> Revisi : Kemahasiswaan
SedangDiprosesKemahasiswaan --> Ditolak : Kemahasiswaan

DiteruskanKeKaurKemahasiswaan --> DiteruskanKeKeuangan : Kaur Kemahasiswaan forward
DiteruskanKeKaurKemahasiswaan --> Revisi : Kaur Kemahasiswaan
DiteruskanKeKaurKemahasiswaan --> Ditolak : Kaur Kemahasiswaan

DiteruskanKeKeuangan --> SedangDiprosesKeuangan : Keuangan review
DiteruskanKeKeuangan --> Revisi : Keuangan
DiteruskanKeKeuangan --> Ditolak : Keuangan

SedangDiprosesKeuangan --> DiteruskanKeKaurKeuangan : Keuangan forward
SedangDiprosesKeuangan --> Revisi : Keuangan
SedangDiprosesKeuangan --> Ditolak : Keuangan

DiteruskanKeKaurKeuangan --> MenungguPersetujuanWD2 : Kaur Keuangan forward\n(setelah isi dana)
DiteruskanKeKaurKeuangan --> Revisi : Kaur Keuangan
DiteruskanKeKaurKeuangan --> Ditolak : Kaur Keuangan

MenungguPersetujuanWD2 --> Disetujui : WD2 approve
MenungguPersetujuanWD2 --> Ditolak : WD2 tolak

Disetujui --> [*]
Ditolak --> [*]
Revisi --> [*]

state BelumDiproses : Pengajuan baru masuk
state Revisi : Dikembalikan ke Ormawa
state Disetujui : Masuk Grafik & Report
@enduml
```

---

## Cara Render

1. **Online**: Copy isi blok `plantuml` ke https://www.plantuml.com/plantuml/uml
2. **VSCode**: Install extension "PlantUML", buka file ini, klik preview
3. **Export PNG/SVG**: Di PlantUML online, klik "Submit" lalu download gambar
