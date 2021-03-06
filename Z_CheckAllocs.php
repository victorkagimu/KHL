<?php
/* $Id$*/
/*This page adds the total of allocation records and compares this to the recorded allocation total in DebtorTrans table */

include('includes/session.inc');
$title = _('Customer Allocations != DebtorTrans.Alloc');
include('includes/header.inc');

/*First off get the DebtorTransID of all invoices where allocations dont agree to the recorded allocation */
$sql = "SELECT debtortrans.id,
		debtortrans.debtorno,
		debtortrans.transno,
		ovamount+ovgst AS totamt,
		SUM(custallocns.Amt) AS totalalloc,
		debtorsmaster.currcode,
		debtortrans.alloc
	FROM debtortrans
	INNER JOIN custallocns
		ON debtortrans.id=custallocns.transid_allocto
	LEFT JOIN debtorsmaster
		ON debtortrans.debtorno=debtorsmaster.debtorno
	WHERE debtortrans.type=10
	GROUP BY debtortrans.ID,
		debtortrans.type=10,
		ovamount+ovgst,
		debtortrans.alloc
	HAVING SUM(custallocns.amt) < debtortrans.alloc - 1";

$result = DB_query($sql,$db);

if (DB_num_rows($result)==0){
	prnMsg(_('There are no inconsistencies with allocations') . ' - ' . _('all is well'),'info');
}

while ($myrow = DB_fetch_array($result)){
	$AllocToID = $myrow['id'];

	echo '<br />' . _('Allocations made against') . ' ' . $myrow['debtorno'] . ' ' . _('Invoice Number') . ': ' . $myrow['transno'];
	echo '<br />' . _('Original Invoice Total') . ': '. locale_money_format($myrow['totamt'], $myrow['currcode']);
	echo '<br />' . _('Total amount recorded as allocated against it') . ': ' . locale_money_format($myrow['alloc'], $myrow['currcode']);
	echo '<br />' . _('Total of allocation records') . ': ' . locale_money_format($myrow['totalalloc'], $myrow['currcode']);

	$sql = "SELECT type,
			transno,
			trandate,
			debtortrans.debtorno,
			reference,
			rate,
			ovamount+ovgst+ovfreight+ovdiscount AS totalamt,
			custallocns.amt
		FROM debtortrans
			INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocfrom
		WHERE custallocns.transid_allocto='". $AllocToID . "'";

	$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
	$TransResult = DB_query($sql,$db,$ErrMsg);

	echo '<table class="selection">';

	$tableheader = '<tr>
				<th>' . _('Type') . '</th>
				<th>' . _('Number') . '</th>
				<th>' . _('Reference') . '</th>
				<th>' . _('Ex Rate') . '</th>
				<th>' . _('Amount') . '</th>
				<th>' . _('Alloc') . '</th></tr>';
	echo $tableheader;

	$RowCounter = 1;
	$k = 0; //row colour counter
	$AllocsTotal = 0;

	while ($myrow1=DB_fetch_array($TransResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		if ($myrow1['type']==11){
			$TransType = _('Credit Note');
		} else {
			$TransType = _('Receipt');
		}
		printf( '<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>',
			$TransType,
			$myrow1['transno'],
			$myrow1['reference'],
			$myrow1['exrate'],
			locale_money_format($myrow1['totalamt'], $myrow['currcode']),
			locale_money_format($myrow1['amt'], $myrow['currcode']));

		$RowCounter++;
		If ($RowCounter == 12){
			$RowCounter=1;
			echo $tableheader;
		}
		//end of page full new headings if
		$AllocsTotal +=$myrow1['amt'];
	}
	//end of while loop
	echo '<tr><td colspan="6" class="number">' . locale_money_format($AllocsTotal, $myrow['currcode']) . '</td></tr>';
	echo '</table><hr>';
}

echo '</form>';

include('includes/footer.inc');

?>