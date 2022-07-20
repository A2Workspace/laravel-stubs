<?php

namespace A2Workspace\Stubs\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class StubGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:a...
                                {filter? : 過濾名稱}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * 要排除的檔案
     *
     * @var string[]
     */
    protected array $excludedNames = [
        'README.md',
    ];

    /**
     * @var string
     */
    const REGEXP_NAMESPACE = '/^namespace (([A-Z][a-zA-Z]+)(\\\[A-Z][a-zA-Z]+)*);/m';

    /**
     * @var string
     */
    const REGEXP_CLASSNAME = '/^(class|abstract class|interface|trait) ([A-Z][a-zA-Z]+)/m';

    /**
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    // =========================================================================
    // = handle
    // =========================================================================

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $stubs = $this->getStubList();

        if ($stubs->isEmpty()) {
            $this->warn('找不到任何 Stub 檔案');

            return;
        }

        // 這邊處理有輸入 filter 參數的場合。
        if ($inputFilter = $this->getFilterInput()) {
            $stubs = $stubs->filter(function (SplFileInfo $file) use ($inputFilter) {
                return Str::contains($file->getRelativePathname(), $inputFilter);
            });

            if ($stubs->isEmpty()) {
                $this->error('找不到符合的 Stub 檔案');

                return false;
            }
        }

        $stub = $this->choiceFromStubList('選擇要使用的 Stub 檔案', $stubs);

        $name = $this->ask('請輸入要注入的名稱');
        $name = trim($name, '\\/');
        $name = str_replace('/', '\\', $name);

        $tasks = $this->prepareTasks($stub);
        foreach ($tasks as $task) {
            $built = $this->buildStub($task, $name);

            $path = $this->getDestinationPath($built);

            if (empty($path)) {
                $this->error("無法處理 {$task->getRealPath()}");
                continue;
            }

            if (! $this->confirmWhenAlreadyExists($path)) {
                $this->warn("略過處理 {$path}");
                continue;
            }

            $this->putFile($path, $built);

            $this->line("已建立 \"{$path}\"");
        }
    }

    /**
     * @return string
     */
    protected function getFilterInput()
    {
        return trim($this->argument('filter'));
    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $stub
     * @return array
     */
    protected function prepareTasks(SplFileInfo $stub): array
    {
        return $stub->isDir()
            ? $this->getStubsInDirectory($stub)
            : [$stub];
    }

    /**
     * 若檔案存在，則詢問使用者是否要覆蓋。
     *
     * @param  string  $path
     * @return bool
     */
    protected function confirmWhenAlreadyExists($path): bool
    {
        if (! $this->files->exists($path)) {
            return true;
        }

        return $this->confirm("{$path} 檔案已存在，是否要覆蓋?", false);
    }

    // =========================================================================
    // = getStubList
    // =========================================================================

    /**
     * 回傳找到的 stub 列表。
     *
     * @return \Illuminate\Support\Collection<int, \Symfony\Component\Finder\SplFileInfo>
     */
    protected function getStubList(): Collection
    {
        $paths = $this->laravel['config']['stubs.paths'] ?? resource_path('stubs');

        return collect($paths)
            ->map(fn ($path) => $this->getStubsInDirectory($path))
            ->collapse();
    }

    /**
     * 回傳指令目錄下的 stub 檔案。排除隱藏的與 $excludedNames 指定的檔案。
     *
     * @param  string  $path
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    protected function getStubsInDirectory(string $path): array
    {
        $finder = Finder::create()
            ->filter(function (SplFileInfo $file) {
                return !in_array($file->getRelativePathname(), $this->excludedNames);
            })
            ->ignoreDotFiles(true)
            ->in($path)
            ->depth(0)
            ->sortByName();

        return iterator_to_array($finder, false);
    }

    // =========================================================================
    // = choiceFromStubList
    // =========================================================================

    /**
     * 讓使用者自 stub 列表中選取一個。
     *
     * @param  string  $question
     * @param  \Illuminate\Support\Collection  $stubs
     * @return \Symfony\Component\Finder\SplFileInfo
     */
    protected function choiceFromStubList($question, Collection $stubs): SplFileInfo
    {
        $stubs = $stubs->map(function (SplFileInfo $file) {
            $label = $file->isDir()
                ? $file->getRelativePathname() . DIRECTORY_SEPARATOR
                : $file->getRelativePathname();

            return [$label, $file];
        });

        $choices = $stubs->pluck(0)->toArray();

        $input = $this->choice($question, $choices);

        return $stubs->first(function ($value) use ($input) {
            return $value[0] === $input;
        })[1];
    }

    // =========================================================================
    // = buildStub
    // =========================================================================

    /**
     * @param  \Symfony\Component\Finder\SplFileInfo  $stub
     * @param  string  $name
     * @return string
     */
    protected function buildStub(SplFileInfo $stub, $name): string
    {
        $pos = strrpos($name, '\\');

        $namespace = (false !== $pos) ? substr($name, 0, $pos) : null;
        $classname = (false !== $pos) ? substr($name, $pos + 1) : $name;

        $contents = $stub->getContents();

        $contents = $this->replaceNamespace($contents, $namespace);
        $contents = $this->replaceClass($contents, $classname);

        return $contents;
    }

    /**
     * @param  string  $contents
     * @param  string  $classname
     * @return string
     */
    protected function replaceClass($contents, $classname): string
    {
        $replace = [
            'Dummy' => Str::ucfirst($classname),
            'Dummies' => Str::ucfirst(Str::plural($classname)),
            'dummy' => Str::lower($classname),
            'dummies' => Str::plural(Str::lower($classname)),
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $contents
        );
    }

    /**
     * 找到內容中的 namespace 宣告行，並將指定的 $namespace 附加在後面。
     *
     * 若 namespace 宣告中已 \Dummy 結尾，則 $namespace 將會附加在 \Dummy 之前。
     *
     * 範例:
     *
     * ```
     * $this->replaceNamespace($contents = 'namespace App\Models;', 'Admin');
     * echo $contents; // => namespace App\Models\Admin;
     *
     * $this->replaceNamespace($contents = 'namespace App\Models\Dummy;', 'Admin');
     * echo $contents; // => namespace App\Models\Admin\Dummy;
     * ```
     *
     * @param  string  $contents
     * @param  string  $namespace
     * @return string
     */
    protected function replaceNamespace($contents, $namespace): string
    {
        if (empty($namespace)) {
            return $contents;
        }

        return preg_replace_callback(static::REGEXP_NAMESPACE, function ($matches) use ($namespace) {
            if ($matches[3] === '\Dummy') {
                $matches[1] = str_replace('\Dummy', '', $matches[1]);
            }

            return str_replace(
                $matches[1],
                "{$matches[1]}\\{$namespace}",
                $matches[0]
            );
        }, $contents);
    }

    // =========================================================================
    // = getDestinationPath
    // =========================================================================

    /**
     * 取得輸出目的之完整路徑。無法處理則回傳 false。
     *
     * @param  string  $built
     * @return string|bool
     */
    protected function getDestinationPath($built)
    {
        // 首先，我們嘗試從檔案內容中取出 namespace 與 class 名稱。
        // 若其中任一無法比對則回傳 false。
        $namespace = $this->resolveStubNamespace($built);
        $classname = $this->resolveStubClassname($built);

        if (! ($namespace && $classname)) {
            return false;
        }

        // 接著，我們嘗試比對 namespace。若符合則生成對應目錄的完整路徑。
        $destinations = [
            $this->laravel->getNamespace() => $this->laravel['path'],
            'Tests\\' => $this->laravel->basePath('tests'),
            'Database\\Factories' => $this->laravel->databasePath('factories'),
            'Database\\Seeders' => $this->laravel->databasePath('seeders'),
        ];

        foreach ($destinations as $rootNamespace => $destination) {
            // 處理 namespace Test\Feature; 這種情形
            if (Str::startsWith($namespace, $rootNamespace)) {
                $relative = Str::replaceFirst($rootNamespace, '', $namespace);
                $relative = str_replace('\\', '/', $relative);
            }
            // 處理 namespace Test; 這種情形
            else if ($namespace === substr($rootNamespace, 0, -1)) {
                $relative = '';
            }
            // 例外則跳過
            else {
                continue;
            }

            $path = [
                $destination,
                $relative,
                "{$classname}.php"
            ];

            $path = join('/', $path);
            $path = str_replace('//', '/', $path);
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

            return $path;
        }

        return false;
    }

    /**
     * @param  string  $contents
     * @return string|bool
     */
    protected function resolveStubNamespace($contents)
    {
        if (preg_match(static::REGEXP_NAMESPACE, $contents, $matches)) {
            return $matches[1];
        }

        return false;
    }

    /**
     * @param  string  $contents
     * @return string|bool
     */
    protected function resolveStubClassname($contents)
    {
        if (preg_match(static::REGEXP_CLASSNAME, $contents, $matches)) {
            return $matches[2];
        }

        return false;
    }

    // =========================================================================
    // = putFile
    // =========================================================================

    /**
     * 寫入檔案到指定路徑。
     *
     * @param  string  $path
     * @param  string  $contents
     * @return int|bool
     */
    protected function putFile($path, $contents)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $this->files->put($path, $contents);
    }
}
