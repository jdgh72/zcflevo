<table class='file_list_table'>

{foreach from=$lid key=rowId item=rowValue}
   <tr class='{cycle values='file_list_row_odd,file_list_row_even'}'>
    <td>{$rowId}</td>
    <td>{$rowValue}</td>
  </tr>
{/foreach}
</table>