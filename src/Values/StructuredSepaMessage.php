<?php

namespace Codelicious\Coda\Values;

class StructuredSepaMessage
{
	/** @var string */
	private $sepaMessage;
	/** @var string */
	private $endToEndReference;
	/** @var string */
	private $paymentReference;
	/** @var string */
	private $mandateReference;
	/** @var string */
	private $structuredReference;
	/** @var string */
	private $instructionReference;
	/** @var string */
	private $remittanceInfo;
	/** @var string */
	private $unstructuredRemittance;
	/** @var string */
	private $transactionType;
	/** @var string */
	private $creditorReference;
	/** @var string */
	private $countryCode;
	/** @var string */
	private $additionalCode;
	/** @var array */
	private $beneficiary;
	/** @var array */
	private $orderingParty;
	/** @var array */
	private $debtor;
	/** @var string */
	private $numberOfTransactions;
	/** @var string */
	private $name;
	/** @var string */
	private $account;

	/**
	 * @param string $sepaMessage Raw SEPA message
	 * @param string $endToEndReference
	 * @param string $paymentReference
	 * @param string $mandateReference
	 * @param string $structuredReference
	 * @param string $instructionReference
	 * @param string $remittanceInfo
	 * @param string $unstructuredRemittance
	 * @param string $transactionType
	 * @param string $creditorReference
	 * @param string $countryCode
	 * @param string $additionalCode
	 * @param array $beneficiary
	 * @param array $orderingParty
	 * @param array $debtor
	 * @param string $numberOfTransactions
	 * @param string $name
	 * @param string $account
	 */
	public function __construct(
		string $sepaMessage = '',
		string $endToEndReference = '',
		string $paymentReference = '',
		string $mandateReference = '',
		string $structuredReference = '',
		string $instructionReference = '',
		string $remittanceInfo = '',
		string $unstructuredRemittance = '',
		string $transactionType = '',
		string $creditorReference = '',
		string $countryCode = '',
		string $additionalCode = '',
		array $beneficiary = array(),
		array $orderingParty = array(),
		array $debtor = array(),
		string $numberOfTransactions = '',
		string $name = '',
		string $account = ''
	)
	{
		$this->sepaMessage = $sepaMessage;
		$this->endToEndReference = $endToEndReference;
		$this->paymentReference = $paymentReference;
		$this->mandateReference = $mandateReference;
		$this->structuredReference = $structuredReference;
		$this->instructionReference = $instructionReference;
		$this->remittanceInfo = $remittanceInfo;
		$this->unstructuredRemittance = $unstructuredRemittance;
		$this->transactionType = $transactionType;
		$this->creditorReference = $creditorReference;
		$this->countryCode = $countryCode;
		$this->additionalCode = $additionalCode;
		$this->beneficiary = $beneficiary;
		$this->orderingParty = $orderingParty;
		$this->debtor = $debtor;
		$this->numberOfTransactions = $numberOfTransactions;
		$this->name = $name;
		$this->account = $account;
	}

	public function getSepaMessage(): string
	{
		return $this->sepaMessage;
	}

	public function getEndToEndReference(): string
	{
		return $this->endToEndReference;
	}

	public function getPaymentReference(): string
	{
		return $this->paymentReference;
	}

	public function getMandateReference(): string
	{
		return $this->mandateReference;
	}

	public function getStructuredReference(): string
	{
		return $this->structuredReference;
	}

	public function getInstructionReference(): string
	{
		return $this->instructionReference;
	}

	public function getRemittanceInfo(): string
	{
		return $this->remittanceInfo;
	}

	public function getUnstructuredRemittance(): string
	{
		return $this->unstructuredRemittance;
	}

	public function getTransactionType(): string
	{
		return $this->transactionType;
	}

	public function getCreditorReference(): string
	{
		return $this->creditorReference;
	}

	public function getCountryCode(): string
	{
		return $this->countryCode;
	}

	public function getAdditionalCode(): string
	{
		return $this->additionalCode;
	}

	/**
	 * @return array Beneficiary party data (name, address, account, id)
	 */
	public function getBeneficiary(): array
	{
		return $this->beneficiary;
	}

	/**
	 * @return array Ordering party data (name, address, account, id)
	 */
	public function getOrderingParty(): array
	{
		return $this->orderingParty;
	}

	/**
	 * @return array Debtor party data (name, address, account, id)
	 */
	public function getDebtor(): array
	{
		return $this->debtor;
	}

	public function getNumberOfTransactions(): string
	{
		return $this->numberOfTransactions;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getAccount(): string
	{
		return $this->account;
	}
}
