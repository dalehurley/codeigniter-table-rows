codeigniter-table-rows
======================

CodeIgniter: Ability to add HTML attributes to a table row - extend CI_Table

Add the MY_Table.php file to your Site's application/libraries folder.

The `_prep_args` method was updated so data can be passed for a row intended to have attributes such as class and id.

The `generate` method was updated so attributes such as class and id can be added to rows.

```php
    //add the row to the rows array
    $rows[]=array('data'=>$row, 'class'=>'warning');
    echo $this->table->generate($rows);
```
or

```php
    $this->table->add_row(array('data'=>$row, 'class'=>'warning'));
```
