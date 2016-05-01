<?php namespace AwatBayazidi\Foundation\Generator;

use AwatBayazidi\Contracts\Generator\Generator;
use AwatBayazidi\Foundation\Generator\GeneratorUtils;
use Illuminate\Config\Repository as Config;
use Illuminate\Console\Command as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use AwatBayazidi\Abzar\Stub;
use AwatBayazidi\Mazhool\Repository;

class ModelGenerator extends Generator
{

    protected $name; //post
    protected $mazhoolName;
    protected $config;
    protected $filesystem;
    protected $defaultNamespace = '';
    protected $console;
    protected $module;
    protected $useSoftDelete = false;
    protected $rememberToken = false;
    protected $fieldsFile;
    protected $inputFields;
    protected $force = false;
    protected $migrate = false;
    protected $paginate = 0;
    protected $backEnd ;
    protected $stubs_replacements=[
        'Scaffold/Common/Model' => ['NAMESPACE_MODEL', 'SOFT_DELETE_IMPORT', 'MODEL_NAME', 'SOFT_DELETE', 'TABLE_NAME', 'SOFT_DELETE_DATES', 'FILLABLES', 'RULES', 'CAST'],
        'Scaffold/Common/Migration' => ['MODEL_NAME_PLURAL', 'TABLE_NAME', 'FIELDS_MIGRATION'],
        'Scaffold/Common/Repository' => ['PAGINATE','LOWER_NAME','NAMESPACE_REPOSITORY','NAMESPACE_MODEL','MODEL_NAME','MODEL_NAME_PLURAL','MODEL_NAME_CAMEL', 'TABLE_NAME'],
        'Scaffold/ControllerApi' => ['NAMESPACE_API_CONTROLLER','NAMESPACE_REQUEST','NAMESPACE_REPOSITORY','NAMESPACE_MODEL','MODEL_NAME','MODEL_NAME_PLURAL','MODEL_NAME_CAMEL','MODEL_NAME_PLURAL_CAMEL', 'TABLE_NAME','LOWER_NAME'],
        'Scaffold/Controller' => ['NAMESPACE_CONTROLLER','NAMESPACE_REQUEST','NAMESPACE_REPOSITORY','NAMESPACE_MODEL','MODEL_NAME','MODEL_NAME_PLURAL','MODEL_NAME_CAMEL','MODEL_NAME_PLURAL_CAMEL', 'TABLE_NAME','LOWER_NAME'],
        'Scaffold/trans' => ['FIELDS_TRANS','MODEL_NAME','MODEL_NAME_PLURAL','MODEL_NAME_CAMEL','MODEL_NAME_PLURAL_CAMEL'],
        'Scaffold/requests/CreateRequest' => ['NAMESPACE_REQUEST','NAMESPACE_MODEL','MODEL_NAME','RULES'],
        'Scaffold/requests/UpdateRequest' => ['NAMESPACE_REQUEST','NAMESPACE_MODEL','MODEL_NAME','RULES'],
        'Scaffold/Views/create.blade' => ['LOWER_NAME','MODEL_NAME_PLURAL_CAMEL','MODEL_NAME_CAMEL','MODEL_NAME'],
        'Scaffold/Views/edit.blade' => ['LOWER_NAME','MODEL_NAME_PLURAL_CAMEL','MODEL_NAME_CAMEL','MODEL_NAME'],
        'Scaffold/Views/fields.blade' => ['FIELDS'],
        'Scaffold/Views/index.blade' => ['PAGINATE_RENDER','LOWER_NAME','MODEL_NAME_PLURAL','MODEL_NAME_PLURAL_CAMEL','MODEL_NAME_CAMEL','MODEL_NAME','FIELDS_HEADERS','FIELDS_BODY'],
        'Scaffold/Views/show.blade' => ['LOWER_NAME','MODEL_NAME_PLURAL_CAMEL','MODEL_NAME_CAMEL','FIELDS_SHOW'],
        'Scaffold/routes' => ['MODEL_NAME_PLURAL_CAMEL','LOWER_NAME','LOWER_NAME','NAMESPACE_CONTROLLER','MODEL_NAME','NAMESPACE_API_CONTROLLER'],
    ];
    protected $stubs_files = [];
    protected $puts_files = [];
    protected $pathsGenerator = [];

    public function __construct($name, $mazhoolName, Repository $module = null, Config $config = null, Filesystem $filesystem = null, Console $console = null) {
        $this->name = $name;
        $this->mazhoolName = $mazhoolName;
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->console = $console;
        $this->module = $module;
        $this->backEnd = null;
        $this->inti();
        $this->fieldsFile = "mazhool/{$this->getMazhoolNameUcfirst()}/fields.json";
    }

    //-----------------------------------------------------------------------
    public function inti()
    {
        $this->pathsGenerator = [
            'Http/Requests/'.$this->getName(),
        ];
        $this->stubs_files = [
            'Scaffold/Common/Migration' => $this->module->getPathGenerator('migration').'/'.date('Y_m_d_His') . "_" . "create_" . $this->getTableName() . "_table.php",
            'Scaffold/Common/Repository' =>$this->module->getPathGenerator('repository').$this->getBackEndPath(). '/'.$this->getNameUcfirst().'Repository.php',

            'Scaffold/trans' =>$this->module->getPathGenerator('lang'). '/en/'.$this->getNameCamel().'.php',
            'Scaffold/requests/CreateRequest' => $this->module->getPathGenerator('request').$this->getBackEndPath().'/'.$this->getNameCamel().'/Create'.$this->getNameUcfirst().'Request.php',
            'Scaffold/requests/UpdateRequest' => $this->module->getPathGenerator('request').$this->getBackEndPath().'/'.$this->getNameCamel().'/Update'.$this->getNameUcfirst().'Request.php',

            'Scaffold/Common/Model' => $this->module->getPathGenerator('model').$this->getBackEndPath().'/'.$this->getNameUcfirst().'.php',
            'Scaffold/ControllerApi' =>$this->module->getPathGenerator('controller_api').$this->getBackEndPath(). '/'.$this->getNameUcfirst().'ApiController.php',
            'Scaffold/Controller' =>$this->module->getPathGenerator('controller').$this->getBackEndPath(). '/'.$this->getNameUcfirst().'Controller.php',
            'Scaffold/Views/create.blade' => $this->module->getPathGenerator('views').$this->getBackEndPath().'/'.$this->getNamePluralCamel().'/'.'create.blade.php',
            'Scaffold/Views/edit.blade' => $this->module->getPathGenerator('views').$this->getBackEndPath().'/'.$this->getNamePluralCamel().'/'.'edit.blade.php',
            'Scaffold/Views/fields.blade' => $this->module->getPathGenerator('views').$this->getBackEndPath().'/'.$this->getNamePluralCamel().'/'.'fields.blade.php',
            'Scaffold/Views/index.blade' => $this->module->getPathGenerator('views').$this->getBackEndPath().'/'.$this->getNamePluralCamel().'/'.'index.blade.php',
            'Scaffold/Views/show.blade' => $this->module->getPathGenerator('views').$this->getBackEndPath().'/'.$this->getNamePluralCamel().'/'.'show.blade.php',
        ];

        $this->puts_files = [
            'Scaffold/routes' => $this->getPathGenerator('routes').'/routes.php',
        ];
    }
    //--------------------------------------------------
    public function getMazhoolName()
    {
        return $this->mazhoolName;
    }
    public function getMazhoolNameUcfirst()
    {
        return Str::ucfirst($this->getMazhoolName());
    }
    //--------------------------------------------------
    public function getName()
    {
        return $this->name;
    }
    public function getNamePlural()
    {
        return Str::plural($this->getName()); //posts
    }

    public function getNameUcfirst()
    {
        return Str::ucfirst($this->getName()); //Post
    }

    public function getNameCamel()
    {
        return Str::camel($this->getName());
       // Str::camel('Hello World')  helloWorld  & Str::camel('my_words') myWords
    }

    public function getNamePluralCamel()
    {
        return Str::camel($this->getNamePlural());
    }
    public function getTableName()
    {
        return strtolower(Str::snake($this->getNamePlural()));
        //Str::snake('MyWords') my_words
    }
    //-----------------------------------------------------------------------
    public function getBackEndPath()
    {
        if(!is_null($this->backEnd)){
            return '/'.$this->backEnd;
        }
        return $this->backEnd;
    }
    public function getBackEnd()
    {
        return $this->backEnd;
    }
    public function setBackEnd($backEnd)
    {
        $this->backEnd = $backEnd;
        return $this;
    }
    //-----------------------------------------------------------------------
    public function isMigrate()
    {
        return $this->migrate;
    }

    public function setMigrate($migrate)
    {
        $this->migrate = $migrate;
        return $this;
    }
    //--------------------------------------------------------
    public function getConfig()
    {
        return $this->config;
    }
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }
    //--------------------------------------------------------
    public function rememberToken($use = false)
    {
        $this->rememberToken = $use;
        return $this;
    }
    public function useSoftDelete($use = false)
    {
        $this->useSoftDelete = $use;
        return $this;
    }

    //--------------------------------------------------------
    public function getFilesystem()
    {
        return $this->filesystem;
    }
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    //--------------------------------------------------------

    public function getPaginate()
    {
        return $this->paginate;
    }

    public function setPaginate($paginate)
    {
        $this->paginate = $paginate;
        return $this;
    }
    //--------------------------------------------------------
    public function getFieldsFile()
    {
        return $this->fieldsFile;
    }
    public function setFieldsFile($fieldsFile)
    {
        $this->fieldsFile = $fieldsFile;
        return $this;
    }
    //--------------------------------------------------------
    public function getInputFields()
    {
        if($this->fieldsFile)
        {
            try
            {
                if(file_exists($this->fieldsFile))
                    $filePath = $this->fieldsFile;
                else
                    $filePath = base_path($this->fieldsFile);

                if(!file_exists($filePath))
                {
                    $this->console->error("Fields file not found");
                    exit;
                }
                $fileContents = $this->filesystem->get($filePath);
                $fields = json_decode($fileContents, true);

                $this->inputFields = GeneratorUtils::validateFieldsFile($fields);
            }
            catch(\Exception $e)
            {
                $this->console->error($e->getMessage());
                exit;
            }
        }else{
            $this->console->error("Fields file not found");
            exit;
        }
        return $this->inputFields;
    }

    //--------------------------------------------------------
    public function getConsole()
    {
        return $this->console;
    }
    public function setConsole($console)
    {
        $this->console = $console;
        return $this;
    }
    //--------------------------------------------------------
    public function getModule()
    {
        return $this->module;
    }
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }
    //--------------------------------------------------------
    public function getFolders()
    {
        return array_values($this->pathsGenerator);
    }

    public function generateFolders()
    {
        foreach ($this->getFolders() as $folder) {
            $path = $this->module->getModulePath($this->getMazhoolName()).'/'.$folder;
            if (!$this->filesystem->isDirectory($path)) {
                $this->filesystem->makeDirectory($path, 0755, true);
            }
            $this->generateGitKeep($path);
        }
    }
    public function generateGitKeep($path)
    {
        $this->filesystem->put($path.'/.gitkeep', '');
    }
    //--------------------------------------------------------
    public function getStuFiles()
    {
        return $this->stubs_files;
    }

    public function getPutFiles()
    {
        return $this->puts_files;
    }

    public function generateStuFiles()
    {
        foreach ($this->getStuFiles() as $stub => $file) {
            $this->getCreateFile($file,$stub,$this->getReplacement($stub),'put');
        }
    }

    public function generatePutFiles()
    {
        foreach ($this->getPutFiles() as $stub => $file) {
            $this->getCreateFile($file,$stub,$this->getReplacement($stub),'append');
        }
    }

    //--------------------------------------------------------
    public function setForce($force)
    {
        $this->force = $force;
        return $this;
    }
    //--------------------------------------------------------
    public function generate()
    {
        $this->inti();
        $name = $this->getName();
        $this->generateFolders();
        $this->generateStuFiles();
        $this->generatePutFiles();
        if ($this->isMigrate()) {
            $this->console->call("awat:mazhool-migrate", [
                'mazhool' => $this->getMazhoolName(),
            ]);
        }
        $this->info("Model [{$name}] created successfully.");
    }

    //--------------------------------------------------------
    protected function getCreateFile($file,$stub,$replacement,$type){
        $path = $this->module->getModulePath($this->getMazhoolName()).$file;
        $path = str_replace('\\', '/', $path);
        if (!$this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0755, true); //0775
        }

        switch ($type) {
            case 'put':
                $this->filesystem->put($path, $this->getStubContents($stub,$replacement));
                $this->console->info("Created : {$path}");
                break;
            case 'prepend':
                $this->filesystem->prepend($path, $this->getStubContents($stub,$replacement));
                $this->console->info("Updated : {$path}");
                break;
            case 'append':
                $this->filesystem->append($path,$this->getStubContents($stub,$replacement));
                $this->console->info("Updated : {$path}");
                break;
            default:
                $this->filesystem->put($path, $this->getStubContents($stub,$replacement));
                $this->console->info("Created : {$path}");
                break;
        }
    }

    protected function getStubContents($stub,$replacement)
    {
        Stub::setBasePath(__DIR__.'/../../mazhool/src/Templates');
        return (new Stub('/'.$stub.'.stub', $replacement))->render();
    }

    protected function getPutContents($content = '',$file = '',$type = 'put')
    {
        $path = $this->module->getModulePath($this->getMazhoolName()).$file;
        $path = str_replace('\\', '/', $path);
        if (! $this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0755, true);//0777
        }
        switch ($type) {
            case 'put':
                $this->filesystem->put($path,$content);
                break;
            case 'prepend':
                $this->filesystem->prepend($path,$content);
                break;
            case 'append':
                $this->filesystem->append($path,$content);
                break;
            default:
                $this->filesystem->put($path,$content);
                break;
        }
        $this->console->info("Update : {$path}");
    }
    //--------------------------------------------------------
    public function getReplacements()
    {
        return $this->stubs_replacements;
    }

    protected function getReplacement($stub)
    {
        $replacements = $this->getReplacements();
        if (!isset($replacements[$stub])) {
            return [];
        }
        $keys = $replacements[$stub];
        $replaces = [];
        foreach ($keys as $key) {
            if (method_exists($this, $method = 'get'.ucfirst(studly_case(strtolower($key))).'Replacement')) {
                $replaces[$key] = call_user_func([$this, $method]);
            } else {
                $replaces[$key] = null;
            }
        }
        return $replaces;
    }
    //--------------------------------------------------------
    protected function getPathGenerator($path){
        return $this->module->getPathGenerator($path);
    }

    //--------------------------------------------------------
    protected function getNamespaceModelReplacement()
    {
        $extra = $this->getBackEnd();
        $this->setDefaultNamespace($this->getPathGenerator('model'));
        return $this->getClassNamespace($extra);
    }
    protected function getNamespaceRequestReplacement()
    {
        $extra = str_replace($this->getClass(), '', $this->getNameUcfirst());
        if(!is_null($this->getBackEnd())){
            $extra = $this->getBackEnd().'\\'.$extra;
        }
        $this->setDefaultNamespace($this->getPathGenerator('request'));
        return $this->getClassNamespace($extra);
    }
    protected function getNamespaceRepositoryReplacement()
    {
      //  $extra = str_replace($this->getClass(), '', $this->getNameUcfirst());
        $extra = $this->getBackEnd();
        $this->setDefaultNamespace($this->getPathGenerator('repository'));
        return $this->getClassNamespace($extra);
    }
    //--------------------------------------------------------
    protected function getPaginateReplacement()
    {
        return $this->getPaginate();
    }
    protected function getPaginateRenderReplacement()
    {
        return '<div class="row">
                    {!! $'.$this->getModelNamePluralCamelReplacement().'->render() !!}
        </div>';
    }
    //--------------------------------------------------------
    protected function getNamespaceApiControllerReplacement()
    {
        $extra = $this->getBackEnd();
        $this->setDefaultNamespace($this->getPathGenerator('controller_api'));
        return $this->getClassNamespace($extra);
    }
    protected function getNamespaceControllerReplacement()
    {
        $extra = $this->getBackEnd();
        $this->setDefaultNamespace($this->getPathGenerator('controller'));
        return $this->getClassNamespace($extra);
    }
    //--------------------------------------------------------
    protected function getSoftdeleteImportReplacement()
    {
        if($this->useSoftDelete){
            return "use Illuminate\\Database\\Eloquent\\SoftDeletes;";
        }
        return "";
    }
    protected function getSoftdeleteReplacement()
    {
        if($this->useSoftDelete){
            return "use SoftDeletes;\n";
        }
        return "";
    }
    protected function getSoftdeleteDatesReplacement()
    {
        if($this->useSoftDelete){
            return "\n\tprotected \$dates = ['deleted_at'];\n";
        }
        return "";
    }
    //--------------------------------------------------------
    protected function getModelNameReplacement()
    {
        return $this->getNameUcfirst();
    }
    protected function getModelNamePluralReplacement()
    {
        return $this->getNamePlural();
    }
    protected function getModelNameCamelReplacement()
    {
        return $this->getNameCamel();
    }
    protected function getModelNamePluralCamelReplacement()
    {
        return $this->getNamePluralCamel();
    }
    //--------------------------------------------------------
    protected function getTableNameReplacement()
    {
        return $this->getTableName();
    }
    //--------------------------------------------------------
    protected function getRulesReplacement()
    {
        $rules = [];
        foreach($this->getInputFields() as $field)
        {
            if(!empty($field['validations']))
            {
                $rule = '"' . $field['fieldName'] . '" => "' . $field['validations'] . '"';
                $rules[] = $rule;
            }
        }
        return implode(",\n\t\t",$rules);
    }
    //--------------------------------------------------------
    protected function getFillablesReplacement()
    {
        $fillables = [];
        foreach ($this->getInputFields() as $field) {
            $fillables[] = '"'.$field['fieldName'].'"';
        }
        return implode(",\n\t\t",$fillables);
    }
    //--------------------------------------------------------
    protected function getCastReplacement()
    {
        $casts = [];
        foreach ($this->getInputFields() as $field) {
            switch ($field['fieldType']) {
                case 'integer':
                    $rule = '"'.$field['fieldName'].'" => "integer"';
                    break;
                case 'double':
                    $rule = '"'.$field['fieldName'].'" => "double"';
                    break;
                case 'float':
                    $rule = '"'.$field['fieldName'].'" => "float"';
                    break;
                case 'boolean':
                    $rule = '"'.$field['fieldName'].'" => "boolean"';
                    break;
                case 'string':
                case 'char':
                case 'text':
                    $rule = '"'.$field['fieldName'].'" => "string"';
                    break;
                default:
                    $rule = '';
                    break;
            }
            if (!empty($rule)) {
                $casts[] = $rule;
            }
        }
        return implode(",\n\t\t",$casts);
    }
    //--------------------------------------------------------
    protected function getFieldsReplacement()
    {
        $stub = 'Scaffold/Views/field.blade';
        $fieldsStr = '';

        foreach ($this->getInputFields() as $field) {
            $label = "{!! Form::label('\$FIELD_NAME\$', '\$FIELD_NAME_TITLE\$:') !!}";
            switch ($field['type']) {
                case 'text':
                    $fields = $label. "\n\t{!! Form::text('\$FIELD_NAME\$', null, ['class' => 'form-control']) !!}"."\n";
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
                case 'textarea':
                    $fields = $label. "\n\t{!! Form::textarea('\$FIELD_NAME\$', null, ['class' => 'form-control']) !!}"."\n";
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
                case 'password':
                    $fields = $label. "\n\t{!! Form::password('\$FIELD_NAME\$', ['class' => 'form-control']) !!}"."\n";
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
                case 'email':
                    $fields = $label. "\n\t{!! Form::email('\$FIELD_NAME\$', null, ['class' => 'form-control']) !!}"."\n";
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
                case 'file':
                    $fields =$label. "\n\t{!! Form::file('\$FIELD_NAME\$') !!}"."\n";
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
                case 'checkbox':
                    $fields =$label. "\n\t{!! Form::checkbox('\$FIELD_NAME\$', 1, true) !!}"."\n";
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
                case 'radio':
                    $fields =$label;
                    if (count($field['typeOptions']) > 0) {
                        $arr = explode(',', $field['typeOptions']);

                        foreach ($arr as $item) {
                            $label = Str::title(str_replace('_', ' ', $item));

                            $fields .= "\n\t<div class=\"radio-inline\">";
                            $fields .= "\n\t\t<label>";

                            $fields .= "\n\t\t\t{!! Form::radio('\$FIELD_NAME\$', '".$item."', null) !!} $label";

                            $fields .= "\n\t\t</label>";
                            $fields .= "\n\t</div>";
                        }
                    }
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
                case 'number':
                    $fields =$label. "\n\t{!! Form::number('\$FIELD_NAME\$', null, ['class' => 'form-control']) !!}"."\n";
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
                case 'date':
                    $fields =$label. "\n\t{!! Form::date('\$FIELD_NAME\$', null, ['class' => 'form-control']) !!}"."\n";
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
                case 'select':
                    $fields =$label. "\n\t{!! Form::select('\$FIELD_NAME\$', \$INPUT_ARR\$, null, ['class' => 'form-control']) !!}";
                    if (count($field['typeOptions']) > 0) {
                        $arr = explode(',', $field['typeOptions']);
                        $inputArr = '[';
                        foreach ($arr as $item) {
                            $inputArr .= " '$item' => '$item',";
                        }
                        $inputArr = substr($inputArr, 0, strlen($inputArr) - 1);
                        $inputArr .= ' ]';
                        $fields = str_replace('$INPUT_ARR$', $inputArr, $fields);
                    } else {
                        $fields = str_replace('$INPUT_ARR$', '[]', $fields);
                    }
                    $fieldsStr .= $this->getStubContents($stub,[
                        'FIELD_INPUT' => $fields,
                        'FIELD_NAME' => $field['fieldName'],
                        'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                    ]);
                    break;
            }
        }
        return $fieldsStr;
    }
    //--------------------------------------------------------
    protected function getFieldsShowReplacement()
    {
        $fieldsStr = '';
        foreach ($this->getInputFields() as $field) {
            $fieldsStr .= $this->getStubContents('Scaffold/Views/show_field.blade',[
                'FIELD_NAME' => $field['fieldName'],
                'FIELD_NAME_TITLE' => Str::title(str_replace('_', ' ', $field['fieldName'])),
                'MODEL_NAME_CAMEL' => $this->getNameCamel(),
                'SPACE' => "\t\t",
                'VER' => "\n",
            ]);
        }
        return $fieldsStr;
    }
    //--------------------------------------------------------
    protected function getFieldsTransReplacement()
    {
        $trans = [];
        foreach($this->getInputFields() as $field)
        {
            $rule = '"' . $field['fieldName'] . '"    =>       "' . $field['fieldName'] . '"';
            $trans[] = $rule;
        }
        return implode(",\n\t\t",$trans);
    }
    //--------------------------------------------------------
    protected function getFieldsHeadersReplacement()
    {
        $headerFields = "";
        foreach($this->getInputFields() as $field)
        {
            $headerFields .= "<th class='text-center'> {{ trans('{$this->getMazhoolName()}::".strtolower($this->getNameCamel()).".".$field['fieldName']."') }} </th>\n\t\t\t\t\t\t";
        }
        return trim($headerFields);
    }
    //--------------------------------------------------------
    protected function getFieldsBodyReplacement()
    {
        $tableBodyFields = "";
        foreach($this->getInputFields() as $field)
        {
            $tableBodyFields .= "<td class='text-center'>{!! $" . $this->getNameCamel() . "->" . $field['fieldName'] . " !!}</td>\n\t\t\t\t\t\t\t";
        }
        return trim($tableBodyFields);
    }
    //--------------------------------------------------------
    protected function getFieldsMigrationReplacement()
    {
        $fieldsStr = "\$table->increments('id');\n";
        foreach($this->getInputFields() as $field)
        {
            $fieldsStr .= SchemaGenerator::createField($field['fieldInput']);
        }
        $fieldsStr .= "\t\t\t\$table->timestamps();";
        if ($this->rememberToken) {
            $fieldsStr .= "\n\t\t\t\$table->rememberToken();";
        }
        if($this->useSoftDelete)
            $fieldsStr .= "\n\t\t\t\$table->softDeletes();";
        return $fieldsStr;
    }
//----------------------- Mazhool ---------------------------------
    protected function getLowerNameReplacement()
    {
        return strtolower($this->getMazhoolName());
    }

    protected function getStudlyNameReplacement()
    {
        return $this->getMazhoolName();
    }
//--------------------------------------------------------
    protected function getModuleNamespaceReplacement()
    {
        return str_replace('\\', '\\\\', $this->module->config('namespace'));
    }
    public function getClass()
    {
        return class_basename($this->name);
    }

    public function getDefaultNamespace()
    {
        return $this->defaultNamespace;
    }

    public function setDefaultNamespace($nsme = '')
    {
         $this->defaultNamespace = str_replace('/', '\\', $nsme);
    }

    public function getClassNamespace($extra='')
    {
        $mazhool = $this->module->findOrFail($this->getMazhoolName());
        $extra = str_replace('/', '\\', $extra);
        $namespace = $this->module->config('namespace');
        $namespace .= '\\'.$mazhool->getStudlyName();
        $namespace .= '\\'.$this->getDefaultNamespace();
        $namespace .= '\\'.$extra;
        return rtrim($namespace, '\\');
    }

    //------------------------------------------------------------------------------
    protected function info($text)
    {
        $line   = '+' . str_repeat('-', strlen($text) + 4) . '+';
        $this->console->info($line);
        $this->console->info("|  $text  |");
        $this->console->info($line);
    }
}
