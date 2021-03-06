<?php
/* $Revision: 1.20 $ */
/* $Id$*/

include('includes/session.inc');
$title = _('Currencies Maintenance');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CurrenciesArray.php');

if (isset($_GET['SelectedCurrency'])){
	$SelectedCurrency = $_GET['SelectedCurrency'];
} elseif (isset($_POST['SelectedCurrency'])){
	$SelectedCurrency = $_POST['SelectedCurrency'];
}

$ForceConfigReload = true;
include('includes/GetConfig.php');

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $title.'</p><br />';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible
	$i=1;

	$sql="SELECT count(currabrev)
			FROM currencies WHERE currabrev='".$_POST['Abbreviation']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]!=0 and !isset($SelectedCurrency)) {
		$InputError = 1;
		prnMsg( _('The currency already exists in the database'),'error');
		$Errors[$i] = 'Abbreviation';
		$i++;
	}
	if (strlen($_POST['Abbreviation']) > 3) {
		$InputError = 1;
		prnMsg(_('The currency abbreviation must be 3 characters or less long and for automated currency updates to work correctly be one of the ISO4217 currency codes'),'error');
		$Errors[$i] = 'Abbreviation';
		$i++;
	}
	if (!is_numeric(filter_number_input($_POST['ExchangeRate']))){
		$InputError = 1;
	   prnMsg(_('The exchange rate must be numeric'),'error');
		$Errors[$i] = 'ExchangeRate';
		$i++;
	}
	if (!is_numeric($_POST['DecimalPlaces'])){
		$InputError = 1;
	   prnMsg(_('The decimal places must be numeric'),'error');
		$Errors[$i] = 'DecimalPlaces';
		$i++;
	}
	if (strlen($_POST['CurrencyName']) > 20) {
		$InputError = 1;
		prnMsg(_('The currency name must be 20 characters or less long'),'error');
		$Errors[$i] = 'CurrencyName';
		$i++;
	}
	if (strlen($_POST['Country']) > 50) {
		$InputError = 1;
		prnMsg(_('The currency country must be 50 characters or less long'),'error');
		$Errors[$i] = 'Country';
		$i++;
	}
	if (strlen($_POST['Country']) == 0) {
		$InputError = 1;
		prnMsg(_('You must enter a country name'),'error');
		$Errors[$i] = 'Country';
		$i++;
	}
	if (strlen($_POST['HundredsName']) > 15) {
		$InputError = 1;
		prnMsg(_('The hundredths name must be 15 characters or less long'),'error');
		$Errors[$i] = 'HundredsName';
		$i++;
	}
	if (mb_strstr($_POST['Abbreviation'],"'") OR mb_strstr($_POST['Abbreviation'],'+') OR mb_strstr($_POST['Abbreviation'],"\"") OR mb_strstr($_POST['Abbreviation'],'&') OR mb_strstr($_POST['Abbreviation'],' ') OR mb_strstr($_POST['Abbreviation'],"\\") OR mb_strstr($_POST['Abbreviation'],'.') OR mb_strstr($_POST['Abbreviation'],'"')) {
		$InputError = 1;
		prnMsg( _('The currency code cannot contain any of the following characters') . " . - ' & + \" " . _('or a space'),'error');
		$Errors[$i] = 'Abbreviation';
		$i++;
	}

	if (isset($SelectedCurrency) AND $InputError !=1) {

		/*SelectedCurrency could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		$sql = "UPDATE currencies SET
					currency='" . $CurrenciesArray[$SelectedCurrency] . "',
					country='". $_POST['Country']. "',
					hundredsname='" . $_POST['HundredsName'] . "',
					decimalplaces='" . $_POST['DecimalPlaces'] . "',
					rate='" .filter_number_input($_POST['ExchangeRate']) . "'
					WHERE currabrev = '" . $SelectedCurrency . "'";

		$msg = _('The currency definition record has been updated');
	} else if ($InputError !=1) {

	/*Selected currencies is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */
		$sql = "INSERT INTO currencies (currency,
						currabrev,
						country,
						hundredsname,
						decimalplaces,
						rate)
				VALUES ('" . $CurrenciesArray[$_POST['Abbreviation']] . "',
						'" . $_POST['Abbreviation'] . "',
						'" . $_POST['Country'] . "',
						'" . $_POST['HundredsName'] .  "',
						'" . $_POST['DecimalPlaces'] .  "',
						'" . filter_number_input($_POST['ExchangeRate']) . "'
						)";

		$msg = _('The currency definition record has been added');
	}
	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	if ($InputError!=1){
		prnMsg( $msg,'success');
	}
	unset($SelectedCurrency);
	unset($_POST['CurrencyName']);
	unset($_POST['Country']);
	unset($_POST['HundredsName']);
	unset($_POST['ExchangeRate']);
	unset($_POST['DecimalPlaces']);
	unset($_POST['Abbreviation']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$sql= "SELECT COUNT(*) FROM debtorsmaster WHERE debtorsmaster.currcode = '" . $SelectedCurrency . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0)
	{
		prnMsg(_('Cannot delete this currency because customer accounts have been created referring to this currency') .
		 	'<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('customer accounts that refer to this currency'),'warn');
	} else {
		$sql= "SELECT COUNT(*) FROM suppliers WHERE suppliers.currcode = '".$SelectedCurrency."'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0)
		{
			prnMsg(_('Cannot delete this currency because supplier accounts have been created referring to this currency')
			 . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('supplier accounts that refer to this currency'),'warn');
		} else {
			$sql= "SELECT COUNT(*) FROM banktrans WHERE banktrans.currcode = '" . $SelectedCurrency . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0] > 0){
				prnMsg(_('Cannot delete this currency because there are bank transactions that use this currency') .
				'<br />' . ' ' . _('There are') . ' ' . $myrow[0] . ' ' . _('bank transactions that refer to this currency'),'warn');
			} elseif ($FunctionalCurrency==$SelectedCurrency){
				prnMsg(_('Cannot delete this currency because it is the functional currency of the company'),'warn');
			} else {
				//only delete if used in neither customer or supplier, comp prefs, bank trans accounts
				$sql="DELETE FROM currencies WHERE currabrev='" . $SelectedCurrency . "'";
				$result = DB_query($sql,$db);
				prnMsg(_('The currency definition record has been deleted'),'success');
			}
		}
	}
	//end if currency used in customer or supplier accounts
}

if (!isset($SelectedCurrency)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCurrency will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of payment termss will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT currency,
					currabrev,
					country,
					hundredsname,
					decimalplaces,
					rate
				FROM currencies";
	$result = DB_query($sql, $db);

	echo '<br /><table class="selection">';
	echo '<tr><td></td>
			<th>' . _('ISO4217 Code') . '</th>
			<th>' . _('Currency Name') . '</th>
			<th>' . _('Country') . '</th>
			<th>' . _('Hundredths Name') . '</th>
			<th>' . _('Decimal Places') . '</th>
			<th>' . _('Exchange Rate') . '</th>
			<th>' . _('Ex Rate - ECB') .'</th>
			</tr>';

	$k=0; //row colour counter
	/*Get published currency rates from Eurpoean Central Bank */
	if ($_SESSION['UpdateCurrencyRatesDaily'] != '0') {
//		$CurrencyRatesArray = GetECBCurrencyRates();
	} else {
		$CurrencyRatesArray = array();
	}

	while ($myrow = DB_fetch_array($result)) {
		if ($myrow[1]==$FunctionalCurrency){
			echo '<tr bgcolor=#FFbbbb>';
		} elseif ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo  '<tr class="OddTableRows">';;
			$k++;
		}
		// Lets show the country flag
		$ImageFile = 'flags/' . strtoupper($myrow[1]) . '.gif';

		if(!file_exists($ImageFile)){
			$ImageFile =  'flags/blank.gif';
		}

		if ($myrow[1]!=$FunctionalCurrency){
			printf('<td><img src="%s" /></td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td><a href="%s&SelectedCurrency=%s">%s</a></td>
					<td><a href="%s&SelectedCurrency=%s&delete=1">%s</a></td>
					<td><a href="%s/ExchangeRateTrend.php?%s">' . _('Graph') . '</a></td>
					</tr>',
					$ImageFile,
					$myrow['currabrev'],
					$myrow['currency'],
					$myrow['country'],
					$myrow['hundredsname'],
					locale_number_format($myrow['decimalplaces'],0),
					locale_number_format($myrow['rate'],5),
					locale_number_format(GetCurrencyRate($myrow['currabrev'],$CurrencyRatesArray),5),
					htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
					$myrow['currabrev'],
					_('Edit'),
					htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
					$myrow['currabrev'],
					_('Delete'),
					$rootpath,
					'CurrencyToShow=' . $myrow['currabrev']);
		} else {
			printf('<td><img src="%s" /></td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td colspan="2">%s</td>
					<td><a href="%s&SelectedCurrency=%s">%s</a></td>
					<td><a href="%s/ExchangeRateTrend.php?%s">' . _('Graph') . '</a></td>
					</tr>',
					$ImageFile,
					$myrow['currabrev'],
					$myrow['currency'],
					$myrow['country'],
					$myrow['hundredsname'],
					$myrow['decimalplaces'],
					1,
					_('Functional Currency'),
					htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
					$myrow['currabrev'],
					_('Edit'),
					htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
					$myrow['currabrev'],
					_('Delete'),
					$rootpath,
					'CurrencyToShow=' . $myrow['currabrev']);
		}

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedCurrency)) {
	echo '<div class="centre"><a href="' .htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8')  . '">'._('Show all currency definitions').'</a></div>';
}

echo '<br />';

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedCurrency) AND $SelectedCurrency!='') {
		//editing an existing payment terms

		$sql = "SELECT currency,
				currabrev,
				country,
				hundredsname,
				decimalplaces,
				rate
				FROM currencies
				WHERE currabrev='" . $SelectedCurrency . "'";

		$ErrMsg = _('An error occurred in retrieving the currency information');;
		$result = DB_query($sql, $db, $ErrMsg);

		$myrow = DB_fetch_array($result);

		$_POST['Abbreviation'] = $myrow['currabrev'];
		$_POST['CurrencyName']  = $myrow['currency'];
		$_POST['Country']  = $myrow['country'];
		$_POST['HundredsName']  = $myrow['hundredsname'];
		$_POST['DecimalPlaces']  = $myrow['decimalplaces'];
		$_POST['ExchangeRate']  = $myrow['rate'];



		echo '<input type="hidden" name="SelectedCurrency" value="' . $SelectedCurrency . '" />';
		echo '<input type="hidden" name="Abbreviation" value="' . $_POST['Abbreviation'] . '" />';
		echo '<table class="selection"><tr>
			<td>' . _('ISO 4217 Currency Code').':</td>
			<td>';
		echo $_POST['Abbreviation'] . '</td></tr>';

	} else { //end of if $SelectedCurrency only do the else when a new record is being entered
		if (!isset($_POST['Abbreviation'])) {$_POST['Abbreviation']='';}
		echo '<table class="selection"><tr>
			<td>' ._('Currency Abbreviation') . ':</td>
			<td><select name="Abbreviation">';
		foreach ($CurrenciesArray as $Abbreviation=>$CurrencyString) {
			$CurrencyName=explode(',', $CurrencyString);
			echo '<option value="'.$Abbreviation.'">'.$CurrencyName[1].'</option>';
		}
		echo '</select></td></tr>';
	}

	echo '<tr><td>'._('Country').':</td>';
	echo '<td>';
	if (!isset($_POST['Country'])) {$_POST['Country']='';}
	echo '<input ' . (in_array('Country',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Country" size="30" maxlength="50" value="' . $_POST['Country'] . '" />';
	echo '</td></tr>';
	echo '<tr><td>'._('Hundredths Name').':</td>';
	echo '<td>';
	if (!isset($_POST['HundredsName'])) {$_POST['HundredsName']='';}
	echo '<input ' . (in_array('HundredsName',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="HundredsName" size="10" maxlength="15" value="'. $_POST['HundredsName'].'" />';
	echo '</td></tr>';
	echo '<tr><td>'._('Exchange Rate').':</td>';
	echo '<td>';
	if (!isset($_POST['ExchangeRate'])) {$_POST['ExchangeRate']='1';}
	if ($myrow[1]!=$FunctionalCurrency){
		echo '<input ' . (in_array('ExchangeRate',$Errors) ?  'class="inputerror"' : '' ) .' type="text" class="number" name="ExchangeRate" size="10" maxlength="9" value="'. locale_number_format($_POST['ExchangeRate'],5).'" />';
	} else {
		echo '<input ' . (in_array('ExchangeRate',$Errors) ?  'class="inputerror"' : '' ) .' readonly type="text" class="number" name="ExchangeRate" size="10" maxlength="9" value="'. locale_number_format($_POST['ExchangeRate'],5).'" />';
	}
	echo '</td></tr>';
	echo '<tr><td>'._('Decimal Places to Show').':</td>';
	echo '<td>';
	if (!isset($_POST['DecimalPlaces'])) {$_POST['DecimalPlaces']=2;}
	echo '<input ' . (in_array('DecimalPlaces',$Errors) ?  'class="inputerror"' : '' ) .' type="text" class="number" name="DecimalPlaces" size="10" maxlength="9" value="'. $_POST['DecimalPlaces'].'" />';
	echo '</td></tr>';
	echo '</table>';

	echo '<br /><div class="centre"><button type="submit" name="submit">'._('Enter Information').'</button></div><br />';

	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>