<?php 
require('../fpdf/fpdf.php');
	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->SetFont("Arial", "B", 14);
	$pdf->Cell(0,0,"File name :  main.cpp",0,0,C);
	$pdf->Text(10,30,"File weight : 161 B");
	$pdf->Text(10,40,"using namespace std;");
	$pdf->SetTextColor(130, 0, 0);
	$pdf->Text(10+20,50,"0 - Function : int main (void)");
	$pdf->SetTextColor(0);
	$pdf->Text(10,60,"Return Type : int ");
	$pdf->Text(10,70,"Argument Type & Name : (void)");
	$pdf->SetTextColor(130, 0, 0);
	$pdf->Text(10+20,80,"1 - Function : float lol (int a, int b)");
	$pdf->SetTextColor(0);
	$pdf->Text(10,90,"Return Type : float");
	$pdf->Text(10,100,"Argument Type & Name : (int a, int b)");
	$pdf->Text(10,110,"Line Count : 15");
	$pdf->Text(10,120,"Number of Function : 2");
	$pdf->Text(10,260,"Fin de Page");
	$pdf->AddPage();
	$pdf->Text(10,260,"Fin de Page");
	$pdf->Output();
?>