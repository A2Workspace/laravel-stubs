# A2Workspace/Laravel-Stubs

一個基於專案的程式模板注入器。

透過在專案中的 `resources/stubs` 目錄下，放置類別的模板文件，然後透過命令快速注入並生成。相比原生的 `artisan make:*` 命令可大大減少編寫時間，且模板檔案可隨版控被 git 紀錄。

目前支援的類別類型：
- 命名空間 App\\* 開頭的類別
- 命名空間 Tests\\* 開頭的類別
- 命名空間 Database\\* 開頭的類別

## Installation | 安裝

此套件尚未發布到 **Packagist** 需透過下列方法安裝：

```
composer config repositories.a2workspace/laravel-stubs vcs https://github.com/A2Workspace/laravel-stubs.git
composer require "a2workspace/laravel-stubs:*"
```

## Usage | 如何使用

現在你可以使用 `make:a..` [Artisan 命令](https://laravel.com/docs/9.x/artisan)來生成類別。該命令將會讀取 `resources/stubs` 下的目錄或 `.php` 檔案，將佔位符依照格式替換為給定的名稱，並依照類別名稱自動生成檔案到相對的路徑。

```
php artisan make:a..
```
