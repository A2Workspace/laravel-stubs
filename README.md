# A2Workspace/Laravel-Stubs

<p align="center"><img src="/.github/animation.gif" alt="Laravel-Stubs demo"></p>

一個基於專案的程式模板注入器。

透過在專案中的 `resources/stubs` 目錄下，放置類別的模板文件，然後透過命令快速注入並生成。相比原生的 `artisan make:*` 命令可大大減少編寫時間，且模板檔案可隨版控被 git 紀錄。

目前支援的類別類型：

-   命名空間 App\\\* 開頭的類別
-   命名空間 Tests\\\* 開頭的類別
-   命名空間 Database\\\* 開頭的類別

## Installation | 安裝

此套件尚未發布到 **Packagist** 需透過下列方法安裝：

```bash
composer config repositories.a2workspace/laravel-stubs vcs https://github.com/A2Workspace/laravel-stubs.git
composer require "a2workspace/laravel-stubs:*"
```

接著使用 `vendor:publish` 命令生成配置文件與 `resources/stubs` 資料夾:

```bash
php artisan vendor:publish --tag=@a2workspace/laravel-stubs
```

預設的 `resources/stubs` 資料夾內附帶一個簡單的範例。

## Usage | 如何使用

現在你可以使用 `make:a...` [Artisan 命令](https://laravel.com/docs/9.x/artisan)來生成類別。該命令將會讀取 `resources/stubs` 下的目錄或 `.php` 檔案，將佔位符依照格式替換為給定的名稱，並依照類別名稱自動生成檔案到相對的路徑。

```bash
php artisan make:a...
```

可以傳入一個搜尋參數:

```bash
php artisan make:a... Example
```

## Development | 如何編寫自己的類別模板

**類別模板** 預設被存放在 `resources/stubs` 目錄下，支援單一 `.php` 檔案或一整個目錄包。檔案名稱並不會影響注入過程，`laravel-stubs` 將會以最終處理完的 `namespace` 作為依據，將生成的檔案放置到專案的相對路徑。

你可以建立一個 **類別模板** 並透過 `Dummy` 佔位符填充類別名稱。

一個簡單的 `Model` 模板如下:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dummy extends Model
{
    protected $table = 'dummies';
}
```

在生成時 `laravel-stubs` 將會依照以下規則替換:

- `Dummy`: 替換為開頭大寫格式的注入名稱
- `Dummies`: 替換為複數型態，開頭大寫格式的注入名稱
- `dummy`: 替換為小寫格式的注入名稱
- `dummies`: 替換為複數型態，小寫格式的注入名稱

處理的原始碼可參考 [StubGeneratorCommand::replaceClass()](https://github.com/A2Workspace/laravel-stubs/blob/1.0.0/src/Commands/StubGeneratorCommand.php#L258)。

詳細則可參考 `resources/stubs` 下預設的模板。
