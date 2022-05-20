<?php

namespace DarkAxi0m\PropelView;


//for php < 8
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}



class PropelViewHelper extends PVController
{

    public function ColumnsTable($map)
    {

        $columns = [];
        foreach ($map->getColumns() as $column) {
            $pk  = $column->isPrimaryKey() ? '<span title="PrimaryKey">🔑</span>' : '';
            $null  = $column->isNotNull() ? 'NotNull' : 'Nullable';

            $class = '';
            if ($column->isPrimaryKey())
                $class .= 'table-warning ';
            if ($column->isForeignKey())
                $class .= 'table-info ';

            $name = "<td>$pk</td><td> " . $column->getPhpName() . "</td>";
            if ($column->isForeignKey()) {

                $url = $this->router->pathFor('propelview.table', ['table' => $column->getRelatedTableName()]);
                $name = "<td><a title='{$column->getRelatedTableName()} {$column->getRelatedColumnName()}' href='$url'>🔗</a></td><td><a title='{$column->getRelatedTableName()}' href='$url'>{$column->getPhpName()}</a></td>";
            }
            $name  = trim($name);


            $columns[] = <<<COL
        <tr class="$class">
           $name   
            <td>{$column->getFullyQualifiedName()}</td>
            <td>{$column->getType()}</td>
            <td>{$column->getSize()}</td>
            <td>$null</td>
            <td></td>
          </tr>
        
COL;
        }
        $columns = implode('', $columns);

        return <<<TABLE
         <div class="table-responsive">
        <table class="table table-striped table-sm">
          <thead>
            <tr>
                 <th scope="col"></th>
                <th scope="col">PhpName</th>
                <th scope="col">FQN</th>
                 <th scope="col">Type</th>
                 <th scope="col">Size</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
                $columns
          </tbody>
        </table>
      </div>

TABLE;
    }




    public function SampleTable($map,  $query)
    {

        $samplehead = [];
        $cols = [];
        foreach ($map->getColumns() as $column) {
            // d($column);
            $fk  = $column->isForeignKey() ? '<span title="ForeignKey">🔗</span>' : '';
            $pk  = $column->isPrimaryKey() ? '<span title="PrimaryKey">🔑</span>' : '';

            $cols[$column->getPhpName()] = $column;
            $samplehead[] = <<<HEAD
            <th scope="col">$pk $fk {$column->getPhpName()}</th>
HEAD;
        }


        $samplehead = implode('', $samplehead);
        $filterdesc = '';


        try {
            $count = $query->count();
            $query = $query->limit(10);

            $filter = [];
            foreach ($_REQUEST as $key => $val) {
                if (str_starts_with($key, 'f_')) {
                    if ($map->hasColumn($key)) {
                        $fcol = $map->getColumn($key);
                        $query =  $query->filterBy($fcol->getPhpName(), $val);
                        $filter[] = $fcol->getPhpName() . '=' . $val;
                    }
                }
            }
            if (sizeof($filter)) {
                $filterdesc = ', (' . implode(',', $filter) . ')';
            }

            $query = $query->find()->toArray();
        } catch (\Throwable $th) {
            $query = $th->getMessage();
            $count = 0;
        }

        $rowcount = 0;
        if (is_array($query)) {
            $sample = [];
            foreach ($query  as $key => $row) {
                $rowcount++;
                $rowstr = '<tr>';
                foreach ($row as $key => $value) {
                    $column = $cols[$key];
                    $rowstr .= "<td title='{$column->getType()}'>";




                    if ($column->isForeignKey()) {
                        $url = $this->router->pathFor('propelview.table', ['table' => $column->getRelatedTableName()]);
                        $url .= "?f_" . $column->getRelatedColumnName() . '=' . $value;
                        $rowstr .= "<a title='{$column->getRelatedTableName()}' href='$url'>";
                    }



                    switch ($column->getType()) {
                        case 'BOOLEAN':
                            $rowstr .= $value ? "✅" : "❎";
                            break;

                        default:
                            $rowstr .= $value;
                            break;
                    }

                    if ($column->isForeignKey()) {
                        $rowstr .= "</a>";
                    }



                    $rowstr .= '</td>';
                }
                $rowstr .= '</tr>';

                $sample[] = $rowstr;
            }
            $sample = implode('', $sample);
        } else {
            $sample = $query;
        }



        return <<<TABLE
        <i>Only Showing $rowcount Rows of $count $filterdesc </i>
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

TABLE;
    }
}
