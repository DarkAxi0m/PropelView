<?php
namespace DarkAxi0m\PropelView;

class PropelViewController 
{

	protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function __get($property)
    {
        if ($this->container->{$property}) {
            return  $this->container->{$property};
        }
    }
    public static function dependencies(\Slim\App $app, $named = null)
    {
        $container = $app->getContainer();
        if (empty($named))
            $named = (new \ReflectionClass(static::class))->getShortName();
		$container[$named] = function ($container) {
            return new static($container);
        };
    }
	
	
    public static function routes($route)
    {
		$named = (new \ReflectionClass(static::class))->getShortName();
        $route->get('[/]', $named .':index')->setName('propelview');
        $route->get('/{table}', $named .':index')->setName('propelview.table');
    }

    public function index($request, $response, $args)
    {

      //This is all just an idea right now, 
        $serviceContainer = \Propel\Runtime\Propel::getServiceContainer();

        $tables =  [];

        foreach ($serviceContainer->getDatabaseMap()->getTables() as $key => $table) {
            $url = $this->router->pathFor('propelview.table', ['table' => $table->getPhpName()]);
            $tables[] = <<<TABLES
<li class="nav-item">
    <a class="nav-link" href="$url">
        <span data-feather="bar-chart-2"></span>
        {$table->getPhpName()}
    </a>
</li>
TABLES;
        }

        $tables = implode('', $tables);
        $tablename = '';
        if (isset($args['table'])) {

            $tablename = $args['table'];
            $tablemapClass = "\Map\\{$tablename}TableMap";
            $queryClass = "\\{$tablename}Query";

            $map = new   $tablemapClass();
            try {
                $query = $queryClass::create()->limit(10)->find();
            } catch (\Throwable $th) {
                $query = $th->getMessage();
            }
            // d($map);

            $columns = [];
            $samplehead = [];
            foreach ($map->getColumns() as $column) {
                // d($column);
                $fk  = $column->isForeignKey() ? '<span title="ForeignKey">🔐</span>' : '';
                $pk  = $column->isPrimaryKey() ? '<span title="PrimaryKey">🔑</span>' : '';
                $null  = $column->isNotNull() ? 'NotNull' : 'Nullable';

                $class = '';
                if ($column->isPrimaryKey())
                    $class .= 'table-warning ';
                if ($column->isForeignKey())
                    $class .= 'table-info ';

                $columns[] = <<<COL
            <tr class="$class">
                <td>$pk $fk {$column->getPhpName()}</td>
                <td>{$column->getFullyQualifiedName()}</td>
                <td>{$column->getType()}</td>
                <td>{$column->getSize()}</td>
                <td>$null</td>
                <td></td>
              </tr>
            
COL;

                $samplehead[] = <<<HEAD
                <th scope="col">$pk $fk {$column->getPhpName()}</th>
HEAD;
            }

            $columns = implode('', $columns);
            $samplehead = implode('', $samplehead);

            if (is_array($query)) {
                $sample = [];
                foreach ($query  as $key => $row) {
                    $rowstr = '<tr>';
                    array_map(function ($v) {
                        return "<td>{$v}</td>";
                    }, $row->toArray());
                    $rowstr .= '</tr>';
                    $sample[] = $sample;
                }
                $sample = implode('', $sample);
            } else {
                $sample = $query;
            }
        }
        echo <<<HTML

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.88.1">
    <title>Proepl View -  $tablename </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

        

    <!-- Favicons -->

<meta name="theme-color" content="#7952b3">


    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>

    
    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">
  </head>
  <body>
    
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
  <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="#">Propel View</a>
  <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  
</header>

<div class="container-fluid">
  <div class="row">
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
      <div class="position-sticky pt-3">
        <ul class="nav flex-column">
        $tables
        </ul>

        
      </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
  
      <h2> $tablename Table</h2>

      <ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="structure-tab" data-bs-toggle="tab" data-bs-target="#structure" type="button" role="tab" aria-controls="structure" aria-selected="true">Structure</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="sample-tab" data-bs-toggle="tab" data-bs-target="#sample" type="button" role="tab" aria-controls="sample" aria-selected="false">Sample Data</button>
  </li>
</ul>
<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active" id="structure" role="tabpanel" aria-labelledby="structure-tab">

  <div class="table-responsive">
        <table class="table table-striped table-sm">
          <thead>
            <tr>
              <th scope="col">PhpName</th>
              <th scope="col">FQN</th>
              <th scope="col">Type</th>
              <th scope="col">Size</th>
              <th scope="col">Flags</th>
            </tr>
          </thead>
          <tbody>
                $columns
          </tbody>
        </table>
      </div>

  </div>
  <div class="tab-pane fade" id="sample" role="tabpanel" aria-labelledby="sample-tab">
  <div class="table-responsive">
        <table class="table table-striped table-sm">
          <thead>
            <tr>
             $samplehead
            </tr>
          </thead>
          <tbody>
                $sample
          </tbody>
        </table>
      </div>
      
      
  
  .</div>
</div>




    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script><script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js" integrity="sha384-zNy6FEbO50N+Cg5wap8IKA4M/ZnLJgzc6w2NqACZaK0u0FXfOWRRJOnQtpZun8ha" crossorigin="anonymous"></script><script src="dashboard.js"></script>
  </body>
</html>

HTML;
    }
}
