<?php

/* $Id$*/

include('includes/session.inc');

$title = _('Search All Sales Orders');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search Sales Orders') . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['completed'])) {
	$completed='=1';
	$ShowChecked='checked="checked"';
} else {
	$completed='>=0';
	$ShowChecked='';
}

if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['OrderNumber'])){
	$OrderNumber = $_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])){
	$OrderNumber = $_POST['OrderNumber'];
}
if (isset($_GET['CustomerRef'])){
	$CustomerRef = $_GET['CustomerRef'];
} elseif (isset($_POST['CustomerRef'])){
	$CustomerRef = $_POST['CustomerRef'];
}
if (isset($_GET['SelectedCustomer'])){
	$SelectedCustomer = $_GET['SelectedCustomer'];
} elseif (isset($_POST['SelectedCustomer'])){
	$SelectedCustomer = $_POST['SelectedCustomer'];
}

if (isset($SelectedStockItem) and $SelectedStockItem==''){
	unset($SelectedStockItem);
}
if (isset($OrderNumber) and $OrderNumber==''){
	unset($OrderNumber);
}
if (isset($CustomerRef) and $CustomerRef==''){
	unset($CustomerRef);
}
if (isset($SelectedCustomer) and $SelectedCustomer==''){
	unset($SelectedCustomer);
}
if (isset($_POST['ResetPart'])) {
		unset($SelectedStockItem);
}

if (isset($OrderNumber)) {
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('Sales Order') . '" alt="" />' .
		' ' . _('Order Number') . ' - ' . $OrderNumber . '</p>';
} elseif (isset($CustomerRef)) {

      If (count($_SESSION['AllowedPageSecurityTokens'])==1 and $SupplierLogin==0){
		If($CustomerRef=='%') {
			unset($CustomerRef);
			prnMsg(_(' A illegal character is input, please enter Ref no. directly'),'warn');
			exit(_(' A illegal character is input, please click Main Manual to return normal status'));
		}
	}

	echo _('Customer Ref') . ' - ' . $CustomerRef;
} else {
	if (isset($SelectedCustomer)) {
		echo _('For customer') . ': ' . $SelectedCustomer .' ' . _('and') . ' ';
		echo '<input type="hidden" name="SelectedCustomer" value="'.$SelectedCustomer.'" />';
	}

	if (isset($SelectedStockItem)) {

		$PartString = _('for the part') . ': <b>' . $SelectedStockItem . '</b> ' . _('and') . ' ' .
			'<input type="hidden" name="SelectedStockItem" value="'.$SelectedStockItem.'" />';

	}
}


if (isset($_POST['SearchParts']) and $_POST['SearchParts']!=''){

	if ($_POST['Keywords']!='' AND $_POST['StockCode']!='') {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']!='') {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if (isset($_POST['completed'])) {
			$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
				stockmaster.units,
				SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem
			FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
				 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
				 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
			WHERE salesorderdetails.completed =1
			AND stockmaster.description LIKE '" . $SearchString. "'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
				stockmaster.units,
				SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem
			FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
				 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
				 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
			WHERE stockmaster.description LIKE '" . $SearchString. "'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
		}

	} elseif ($_POST['StockCode']!=''){

		if (isset($_POST['completed'])) {
			$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
				SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem,
				stockmaster.units
			FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
				 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
				 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
			WHERE salesorderdetails.completed =1
			AND stockmaster.stockid LIKE  '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
				SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem,
				stockmaster.units
			FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
				 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
				 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
			WHERE stockmaster.stockid LIKE '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
		}

	} elseif ($_POST['StockCode']=='' AND $_POST['Keywords']=='' AND $_POST['StockCat']!='') {

		if (isset($_POST['completed'])) {
			$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
				SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem,
				stockmaster.units
			FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
				 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
				 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
			WHERE salesorderdetails.completed=1
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qoo,
				SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qdem,
				stockmaster.units
			FROM (((stockmaster LEFT JOIN salesorderdetails on stockmaster.stockid = salesorderdetails.stkcode)
				 LEFT JOIN locstock ON stockmaster.stockid=locstock.stockid)
				 LEFT JOIN purchorderdetails on stockmaster.stockid = purchorderdetails.itemcode)
			WHERE stockmaster.categoryid='" . $_POST['StockCat'] . "'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
		}

	}

	if (mb_strlen($SQL)<2){
		prnMsg(_('No selections have been made to search for parts') . ' - ' . _('choose a stock category or enter some characters of the code or description then try again'),'warn');
	} else {

		$ErrMsg = _('No stock items were returned by the SQL because');
		$DbgMsg = _('The SQL used to retrieve the searched parts was');
		$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

		if (DB_num_rows($StockItemsResult)==1){
		  	$myrow = DB_fetch_array($StockItemsResult);
		  	$SelectedStockItem = $myrow['stockid'];
			$_POST['SearchOrders']='True';
		  	unset($StockItemsResult);
		  	echo '<br />' . _('For the part') . ': ' . $SelectedStockItem . ' ' . _('and') . ' <input type="hidden" name="SelectedStockItem" value="'.$SelectedStockItem.'" />';
		}
	}
} else if (isset($_POST['SearchOrders']) AND Is_Date($_POST['OrdersAfterDate'])==1) {

	//figure out the SQL required from the inputs available
	if (isset($OrderNumber)) {
			$SQL = "SELECT salesorders.orderno,
					debtorsmaster.name,
					debtorsmaster.currcode,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
				FROM salesorders,
					salesorderdetails,
					debtorsmaster,
					custbranch
				WHERE salesorders.orderno = salesorderdetails.orderno
				AND salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND debtorsmaster.debtorno = custbranch.debtorno
				AND salesorders.orderno='". $OrderNumber ."'
				AND salesorders.quotation=0
				AND salesorderdetails.completed".$completed."
				GROUP BY salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto
				ORDER BY salesorders.orderno";
	} elseif (isset($CustomerRef)) {
	      if (count($_SESSION['AllowedPageSecurityTokens'])==1 and $SupplierLogin==0){
		  If($CustomerRef=='%') {
			unset($CustomerRef);
			prnMsg(_(' A illegal character is input, please enter Ref no. directly'),'warn');
			exit(_(' A illegal character is input, please click Main Manual to return normal status'));
		}
	}
			$SQL = "SELECT salesorders.orderno,
					debtorsmaster.name,
					debtorsmaster.currcode,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
				FROM salesorders,
					salesorderdetails,
					debtorsmaster,
					custbranch
				WHERE salesorders.orderno = salesorderdetails.orderno
				AND salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = debtorsmaster.debtorno
				AND debtorsmaster.debtorno = custbranch.debtorno
				AND salesorders.customerref like '%". $CustomerRef."%'
				AND salesorders.quotation=0
				AND salesorderdetails.completed".$completed."
				GROUP BY salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto
				ORDER BY salesorders.orderno";

	} else {
		$DateAfterCriteria = FormatDateforSQL($_POST['OrdersAfterDate']);

		if (isset($SelectedCustomer) AND !isset($OrderNumber) AND !isset($CustomerRef)) {

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						debtorsmaster.currcode,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.branchcode = custbranch.branchcode
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorderdetails.stkcode='". $SelectedStockItem ."'
					AND salesorders.debtorno='" . $SelectedCustomer ."'
					AND salesorders.orddate >= '" . $DateAfterCriteria ."'
					AND salesorders.quotation=0
					AND salesorderdetails.completed".$completed."
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto
					ORDER BY salesorders.orderno";
			} else {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						debtorsmaster.currcode,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.debtorno='" . $SelectedCustomer . "'
					AND salesorders.orddate >= '" . $DateAfterCriteria . "'
					AND salesorders.quotation=0
					AND salesorderdetails.completed".$completed."
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto
					ORDER BY salesorders.orderno";
			}
		} else { //no customer selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						debtorsmaster.currcode,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorderdetails.stkcode='". $SelectedStockItem ."'
					AND salesorders.orddate >= '" . $DateAfterCriteria . "'
					AND salesorders.quotation=0
					AND salesorderdetails.completed".$completed."
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto
					ORDER BY salesorders.orderno";
			} else {
				$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						debtorsmaster.currcode,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverto,
						salesorders.deliverydate, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders,
						salesorderdetails,
						debtorsmaster,
						custbranch
					WHERE salesorders.orderno = salesorderdetails.orderno
					AND salesorders.debtorno = debtorsmaster.debtorno
					AND salesorders.branchcode = custbranch.branchcode
					AND debtorsmaster.debtorno = custbranch.debtorno
					AND salesorders.orddate >= '".$DateAfterCriteria . "'
					AND salesorders.quotation=0
					AND salesorderdetails.completed".$completed."
					GROUP BY salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto
					ORDER BY salesorders.orderno";
			}
		} //end selected customer
	} //end not order number selected

	$SalesOrdersResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo '<br />' . _('No orders were returned by the SQL because') . ' ' . DB_error_msg($db);
		echo '<br />'. $SQL;
	}

}//end of which button clicked options

if (!isset($_POST['OrdersAfterDate']) OR $_POST['OrdersAfterDate'] == '' OR ! Is_Date($_POST['OrdersAfterDate'])){
	$_POST['OrdersAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
}
echo '<table class="selection">';

if (isset($PartString)) {
	echo '<tr><td>'.$PartString.'</td>';
} else {
	echo '<tr><td></td>';
}

if (!isset($OrderNumber) or $OrderNumber==''){
	echo '<td>' . _('Order Number') . ':</td><td>' . '<input type="text" name="OrderNumber" maxlength="8" size="9" /></td><td>' . _('for all orders placed after') .
			': </td><td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'"  name="OrdersAfterDate" maxlength="10" size="11" value="' . $_POST['OrdersAfterDate'] . '" /></td><td>' .
			'<input type="submit" name="SearchOrders" value="' . _('Search Orders') . '" /></td></tr>';
	echo '<tr><td></td><td>' . _('Customer Ref') . ':</td><td>' . '<input type="text" name="2CustomerRef" maxlength="8" size="9" /></td>
			<td></td><td colspan="2"><input type="checkbox" '.$ShowChecked.' name="completed" />'._('Show Completed orders only') . '</td></tr>';
}
echo '</table>';

if (!isset($SelectedStockItem)) {
	$SQL="SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
	$result1 = DB_query($SQL,$db);

   echo '<br />';
   echo '<div class="page_help_text"><font size="1">' . _('To search for sales orders for a specific part use the part selection facilities below') . '   </font></div>';
   echo '<br /><table class="selection">';
   echo '<tr><td><font size="1">' . _('Select a stock category') . ':</font>';
   echo '<select name="StockCat">';

	while ($myrow1 = DB_fetch_array($result1)) {
		if (isset($_POST['StockCat']) and $myrow1['categoryid'] == $_POST['StockCat']){
			echo '<option selected="True" value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		}
	}

   echo '</select>';
   echo '<td><font size="1">' . _('Enter text extracts in the description') . ':</font></td>';
   echo '<td><input type="text" name="Keywords" size="20" maxlength="25" /></td></tr>';
   echo '<tr><td></td>';
   echo '<td><font size="3"><b> ' ._('OR') . ' </b></font><font size="1">' . _('Enter extract of the Stock Code') . ':</font></td>';
   echo '<td><input type="text" name="StockCode" size="15" maxlength="18" /></td>';
   echo '</tr>';
   echo '<tr><td colspan="4"><div class="centre"><input type="submit" name="SearchParts" value="' . _('Search Parts Now') . '" />';

   if (count($_SESSION['AllowedPageSecurityTokens'])>1){
		echo '<input type="submit" name="ResetPart" value="' . _('Show All') . '" /></div>';
   }
   echo '</td></tr></table>';

}

if (isset($StockItemsResult)) {

	echo '<br /><table cellpadding="2" class="selection">';

	$TableHeadings = '<tr><th>' . _('Code') . '</th>' .
				'<th>' . _('Description') . '</th>' .
				'<th>' . _('On Hand') . '</th>' .
				'<th>' . _('Purchase Orders') . '</th>' .
				'<th>' . _('Sales Orders') . '</th>' .
				'<th>' . _('Units') . '</th></tr>';

	echo $TableHeadings;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		printf('<td><font size="1"><input type="submit" name="SelectedStockItem" value="%s" /></font></td>
			<td><font size="1">%s</font></td>
			<td class="number"><font size="1">%s</font></td>
			<td class="number"><font size="1">%s</font></td>
			<td class="number"><font size="1">%s</font></td>
			<td><font size="1">%s</font></td></tr>',
			$myrow['stockid'],
			$myrow['description'],
			$myrow['qoh'],
			$myrow['qoo'],
			$myrow['qdem'],
			$myrow['units']);

//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if stock search results to show

if (isset($SalesOrdersResult)) {

/*show a table of the orders returned by the SQL */

	echo '<br /><table cellpadding="2" width="90%" class="selection">';

	$tableheader = '<tr><th>' . _('Order') . ' #</th>
			<th>' . _('Customer') . '</th>
			<th>' . _('Branch') . '</th>
			<th>' . _('Cust Order') . ' #</th>
			<th>' . _('Order Date') . '</th>
			<th>' . _('Req Del Date') . '</th>
			<th>' . _('Delivery To') . '</th>
			<th>' . _('Order Total') . '</th></tr>';

	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($SalesOrdersResult)) {


		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		$ViewPage = $rootpath . '/OrderDetails.php?OrderNumber=' . $myrow['orderno'];
		$FormatedDelDate = ConvertSQLDate($myrow['deliverydate']);
		$FormatedOrderDate = ConvertSQLDate($myrow['orddate']);
		$FormatedOrderValue = locale_money_format($myrow['ordervalue'],$myrow['currcode']);

		printf('<td><a href="%s">%s</a></td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>',
			$ViewPage,
			$myrow['orderno'],
			$myrow['name'],
			$myrow['brname'],
			$myrow['customerref'],
			$FormatedOrderDate,
			$FormatedDelDate,
			$myrow['deliverto'],
			$FormatedOrderValue);

//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}

echo '</form>';
include('includes/footer.inc');

?>