<?php

// fetch missing taxa via ALA

require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' , 'afd');

$stack = array();

$done = array();

//----------------------------------------------------------------------------------------
function have_already($id)
{
	global $db;
	global $done;
	
	$have = false;
	
	if (in_array($id, $done)) return true;
	
	$sql = 'SELECT * FROM afd WHERE TAXON_GUID="' . $id . '" LIMIT 1';
	
	echo $sql;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1) 
	{		
		$have = true;
	}
	
	return $have;
}

//----------------------------------------------------------------------------------------
function fetch_ala($id)
{
	global $stack;
	
	$url = "https://bie.ala.org.au/ws/species/urn:lsid:biodiversity.org.au:afd.taxon:" . $id . ".json";

	$opts = array(
	  CURLOPT_URL =>$url,
	  CURLOPT_FOLLOWLOCATION => TRUE,
	  CURLOPT_RETURNTRANSFER => TRUE
	);
	
	$ch = curl_init();
	curl_setopt_array($ch, $opts);
	$data = curl_exec($ch);
	$info = curl_getinfo($ch); 
	curl_close($ch);
	
	if ($data != '')
	{
		$obj = json_decode($data);
		
		print_r($obj);
		
		if (!have_already($id))
		{
		
			$row = new stdclass;
			$row->TAXON_GUID = $id;
			$row->CONCEPT_GUID = $row->TAXON_GUID;
			$row->RANK = $obj->taxonConcept->rankString;
			$row->NAME_TYPE = "Valid name";
		
			if (isset($obj->taxonConcept->parentGuid))
			{
				$row->PARENT_TAXON_GUID = $obj->taxonConcept->parentGuid;
				$row->PARENT_TAXON_GUID = str_replace('urn:lsid:biodiversity.org.au:afd.taxon:', '', $row->PARENT_TAXON_GUID);
				$stack[] = $row->PARENT_TAXON_GUID;
			}
		
			$row->NAMES_VARIOUS = $obj->taxonConcept->nameString;
			$row->SCIENTIFIC_NAME = $obj->taxonConcept->nameString;
		
			$row->NAME_GUID = 'unknown';
		
			//print_r($row);
		
			$keys = array();
			$values = array();
		
			foreach ($row as $k => $v)
			{
				$keys[] = $k;
				$values[] = '"' . addcslashes($v, '"') . '"';
			}
		
			echo 'REPLACE INTO afd(' . join(',', $keys) . ') VALUES(' . join(',', $values) . ');' . "\n";
		}
		
		if (isset($obj->classification->classGuid))
		{
			$stack[] = str_replace('urn:lsid:biodiversity.org.au:afd.taxon:', '', $obj->classification->classGuid);
		}
		if (isset($obj->classification->kingdomGuid))
		{
			$stack[] = str_replace('urn:lsid:biodiversity.org.au:afd.taxon:', '', $obj->classification->kingdomGuid);
		}
		if (isset($obj->classification->phylumGuid))
		{
			$stack[] = str_replace('urn:lsid:biodiversity.org.au:afd.taxon:', '', $obj->classification->phylumGuid);
		}
		if (isset($obj->classification->subclassGuid))
		{
			$stack[] = str_replace('urn:lsid:biodiversity.org.au:afd.taxon:', '', $obj->classification->subclassGuid);
		}
		if (isset($obj->classification->familyGuid))
		{
			$stack[] = str_replace('urn:lsid:biodiversity.org.au:afd.taxon:', '', $obj->classification->familyGuid);
		}

		//print_r($stack);
	
	}
			
}	


$stack = array('33a184da-8c74-431a-bb83-89c1d2b54c1c');

$stack = array('31e391dc-8eb4-4bd0-88ac-96b1499d0f56');

$stack = array('e598159f-c526-4842-8f73-916367bffa2a');

$stack = array('591404da-cd0d-413f-b690-246e5ee7b7cb');

$stack = array('5ed80139-31bb-48a8-9f57-42d8015dacbb');

$stack = array('96477a6b-0903-4332-ad91-c362ae0e677e');

// all orders
$stack = array(
"b449f377-ba84-46a1-b804-5d4160d6ece6",
"08d174ee-0285-49c4-84c4-18e493ccc070",
"942d4e5c-a3b0-4a08-a5ab-68584949e83e",
"287e5f7c-d1a7-4b4d-9aea-8be7e72b7e03",
"23ac8470-dcc4-4f81-a397-13d5daea6b9c",
"a8d80125-25ef-4602-b8b7-4556f8fe39ac",
"e4bcfd52-181f-4f2b-8625-74a4fa94c35d",
"bce51ca0-b866-4c80-88d6-2e304459320d",
"84ba5112-6f53-4969-a352-977b785cd533",
"59ebab13-a7fa-4e57-a0d2-12d60500a66f",
"10ba8196-7d57-4df2-9b5a-1a354dc5ba8c",
"1f1c1108-e3d7-4c80-a324-14381a2bf277",
"4a0fa366-926e-4a5a-bfd0-7c9df79bfec7",
"90f0f473-74ca-479e-b301-aa97a823adaf",
"a0dd19ea-e2e9-413f-a2b9-c417b784e336",
"fc34a5f2-afd6-49ad-a587-29dbe0e65f5e",
"20baea37-af09-45e8-86be-cf1509eac7ce",
"2b8ea223-0c7b-4179-b136-2924944e67d1",
"197e9ce2-86dc-4b8d-85f3-fe4b16209dd7",
"ca450127-b4b2-44c5-8d79-6c8a2deebe86",
"56ce6da6-aec8-42e3-80b8-fe0e1e24ff20",
"de32edd9-f4b6-4910-8874-8b0d1cbbfd4a",
"9dd914ae-48f1-42c1-85b8-118b667a665c",
"344516a1-995e-4f2b-b98d-7aaa3d4cf8c2",
"79790449-3610-445e-b6fc-7e7190b9eca8",
"4df4e681-2740-4b46-bd6d-d76445b9f3e5",
"14f9d40a-2346-4c48-9264-89a14c998d1e",
"97c84c4b-a886-4ad1-b060-e6443ad16bad",
"3d608c7a-512f-477a-aac0-a2314c5c3a35",
"337d26d5-9760-4204-97ed-71441cabcf83",
"c744fd9f-1b17-4b66-908a-f9146a7a6bc3",
"de0edaae-fd4a-4d37-8c7d-fb4899c81ef7",
"f2d90841-d25b-4c5d-bdb8-86dcf0a7d468",
"f6339b40-2c19-4586-af24-bcac081fecb8",
"a4b3ea08-4778-45ab-bfc1-b72b00d4d212",
"9b1718d6-ea1b-4784-83f3-67fff0f9664a",
"7a329391-2c9c-4577-911e-a21f4838b2e2",
"966ee64d-6992-43f0-8d72-626f65a63f0f",
"b8bb0d5b-27f3-4cd9-a9e6-4806dfadb5aa",
"810b757f-4501-45f1-9ec4-5e183deb56a5",
"06ffb702-fe8f-4a07-a268-2ea0eb0c06f6",
"065763e7-e00d-4e5d-a9d0-99b290c4b2c8",
"4561737f-69f7-4dc2-9708-ff286d471658",
"3c1eb2aa-246b-4566-a648-48a9206da148",
"7696bb4c-b806-404a-9774-58d13b58d6ad",
"63b779da-c0c7-43dd-bbd8-aed8e591b3f2",
"e41cd2f4-1892-430d-a056-6b5ff8d7d699",
"0cf6680a-11cc-444e-9e8b-738c346add38",
"81d9bbaf-e18d-4dae-9d45-c5b167a582b6",
"bb0e0104-0327-41a4-9d25-77d25d53653a",
"2de86f93-709e-4b50-8544-59bc618dc859",
"3102979f-3a46-427d-8a77-59f3db576899",
"912d7410-285d-437b-bcd7-205b9c884ee5",
"606c5833-fe64-45c0-bf77-c383e6d5cabd",
"e3656863-c6b9-4ba3-bdc2-08ef5504863a",
"0f6b1628-9b03-4d51-a335-0afba1e4c0ad",
"5fe88fc7-9dc5-4e4f-b9d1-81420e1ca402",
"16593316-99bc-4a01-9e39-88d9dd70fa64",
"335eb146-c5fe-439e-9127-1c296f18cc92",
"2dd7d4b4-39b0-44c0-ae0d-94988dd4edeb",
"6cf19397-b075-4269-bba2-2f8d704acfbd",
"4c329b8b-f386-4db7-aeb0-f9bf7b5bcd32",
"2482ab93-b3a9-43db-8476-ca75a945f9bc",
"00988d5f-ce9e-49ca-8e01-f19a9d18eb6b",
"055dcee6-355d-4583-becd-8610cbc38c18",
"5e93c949-36bb-4d2e-89fa-e2570390a065",
"6e94126a-ac35-402c-9375-92ce7bc779aa",
"6c2e06a2-43e6-4d7b-b102-84a0ae0c3d1c",
"e50beffb-c76c-437a-9256-fa7413f518fc",
"6daded85-89a2-4617-bdd3-06b01883bf16",
"d8cce179-a5b5-4be0-bf3c-415b16a3ac74",
"ce6983a8-3296-40e3-bfc2-927e6bdfd234",
"310aba89-1fc0-4404-b97a-d8fcc3a9c6ff",
"b37b68c8-9e4b-4d00-9032-4c5e84d7b33f",
"dbc4f4ad-0ad5-4813-9275-95b00b448832",
"5f4ab3ca-af7c-40e0-acb6-18c930ca40d4",
"7d0c7db7-6e86-4c63-bb4c-ca80c1b84a06",
"e8c753bf-8912-4d7c-b8ea-e8dfa19243f5",
"b38b91a8-e707-49d6-b51c-7988d7698d19",
"5bdfce58-8997-4993-8759-c241201ac7c4",
"a1f52616-f7a6-40b0-86a7-56731de70c7f",
"7a8f812e-1ae0-411e-9c45-b7f5b161029e",
"5a15daa4-a543-4e76-bc55-8d81b18634e3",
"0d878f38-ade9-49de-bb82-72c2f4d3aad6",
"34d1c5a6-ee3a-4bb8-977e-b0e4347e3203",
"068037c9-b634-4c47-ab80-0f3053dd36a2",
"0f538bb8-74c0-426e-b9e1-2a6004b1f85f",
"59c12771-49fb-4354-b9e9-1d913c747a69",
"492392a9-fb1d-459d-ad50-961b9c8a68bb",
"9f2492a9-9a18-4d73-bb88-70739af9df6d",
"91120b87-24d1-4bfc-a51f-ec2650ab121a",
"a89916c7-15c3-47cd-b860-f9f846847b6d",
"cba7884e-cb48-41ca-9fb4-7ee9e4ce7490",
"93e2d27d-1366-4412-890d-6eb5d0bd0183",
"41af2fa8-ed80-40a8-a328-988f870d0e2c",
"06e77dbe-38b8-4e6f-87bb-7ffc68494ca8",
"bfccfd62-9eb5-45f8-994c-df79e8589564",
"4f1b8ffd-a92d-4e12-a305-0282ac10a7ef",
"18c8a8c4-a104-4e7c-b0af-0522b0880398",
"5c67f36f-8069-4de9-b2d0-f0bbd1d602e7",
"cea93f95-25b0-460c-ae42-6402bd6a9478",
"a4e2bd2b-f10b-466b-ac8f-4afeb9d9cf99",
"90d5f33e-e2b2-4f45-b189-d0d454f16866",
"7d7e32e5-a0b3-4213-acf0-010cc8366862",
"ce3ffea0-6010-43c1-9348-ab5f22f51f01",
"f0ac3e6a-4bad-46df-997b-93e985575ce6",
"64da11de-17e1-4fd4-8c1b-860538d5782e",
"36c7d85d-e903-44da-a99e-a57d00d8c51c",
"d1f672e6-e641-4142-b95c-b120b34ac704",
"0521b057-bd9d-4956-a057-ec692fbcdd04",
"795dc023-4aeb-45e5-adf9-4a5dac375087",
"ce146963-b00c-41f5-8012-f65c955a7756",
"ee8b877e-365a-4759-bc74-701117a4c26a",
"69418175-4b6c-497a-9ce8-c3d305d7794b",
"e6fd5832-f2e7-46e3-bc8c-75b5d248233a",
"e1d096d8-e803-4626-a6b6-d0ac7aa49311",
"2488e290-509c-4cec-8462-1c40f91b4ca1",
"8ae036c3-0f00-4a26-8efb-874e0f05952b",
"396e9b89-868a-49b4-84fc-a2c85f001d56",
"5a5c4896-b9ac-4f2d-be3b-c0e644eaf132",
"8f0718a8-7215-488d-97de-bc77e982b3c2",
"02405ba5-24e6-4167-b8e2-f87ae930ae2f",
"118adb5b-ab4d-4b2c-9d85-3112868c6889",
"afc4768a-f827-4624-8916-e1e39172a0ce",
"a96a2f84-12b4-49d7-9c67-5275eda8e74e",
"cefc577b-6ce4-46d1-a944-deea34c58a54",
"6c88f3de-7d6f-42db-b41d-1c42cfa2db0b",
"bd7ddb34-77b1-4e36-96d5-607e2e1f15c4",
"baa4270a-c37d-439d-8754-aae230935b14",
"a97474ee-1982-4dbb-975f-60dac03668b9",
"82f9f1a5-3ba3-49fa-aa5c-3f1c3aebbe50",
"1e145540-49f7-4f49-b194-bc0ad362f040",
"110c8d6d-f2ea-425f-a857-605b7ba940de",
"2eca41a8-4c3d-42c3-bf1b-42672890d2b0",
"a4b82b4d-be1e-4de8-99d0-5048054f1fa8",
"30c2f86e-3ca4-4610-a6bf-fc360369db9a",
"d09bc16f-eb9e-4160-bc96-65957f06db67",
"901d6135-f156-4d8e-9a48-c73510dcb8e0",
"2b34ea23-66c1-4fee-869d-f47f59f3eebe",
"5379d536-1a42-42ce-bd54-5c7cfce165a6",
"0dfb2e86-c006-41a0-b217-469e009e4e96",
"d5ffc8cc-07dd-4eb3-9a83-91f71e828c15",
"d7125a9c-ebab-4e4b-af72-9b88072c6690",
"50b37b5a-0a35-4af9-b4fe-c8c5b5956ac4",
"d1a0221b-3973-4b4b-9923-7a7f53dd2ad0",
"7490ccd5-97f3-44cd-b4ed-e3047bfec15d",
"b032d3de-c9c9-4dcd-8ecd-6395e49ffb2c",
"e62237ea-a001-4323-beb9-48be1df2ee06",
"4aa37015-5ddd-4e99-86a7-f260eba9b30c",
"fa109630-10e1-4ca9-b3d3-bb85cb80bccb",
"7fd4a653-5a75-460a-83f0-88112b7491df",
"166a65a5-a9b4-4daa-88f3-1f99855fad21",
"b263fae6-12c7-4e02-b27a-03622ec8a34f",
"c145b6d6-5fe5-47e1-8b57-1398536edc90",
"c035b661-421f-4fbe-8377-101a08175db8",
"790b55cd-3458-432f-ae29-8d65b9bab746",
"c04b4ca9-995c-4cfe-b83b-cfa2b4fc80cd",
"08371787-659c-4546-b4db-b5d6b82eb391",
"a18a5e21-41a4-426b-bfb8-53d47d598fcf",
"6682a8d0-1e1e-4d09-995d-25f7ba4e9505",
"1e0f9c12-47e9-40c2-bbfa-3d1af2517fbf",
"cb5cb08f-e457-4668-8781-89f8969571cf",
"d4dfb424-3954-4a62-b70c-c9ea613868a5",
"5da3c5f0-8f2f-4a83-8460-de7dac78d957",
"3d4cf99e-60de-48b3-8cf8-5ecbc22cd370",
"f1fb79d7-901e-43c5-965a-2464d3862751",
"6b5750a7-fe18-4684-8a4d-223449846e78",
"c4a10fd6-7ac1-4e30-8609-c4537852f80c",
"dfaf127c-1a01-4fbd-a777-f79d7fc96ce7",
"20ad7d6b-9254-4fe7-a9ea-4d48120fa393",
"64ce6eba-2a43-451f-812e-8692b97b4488",
"bef74583-6f27-4ddf-b2ab-7e5b601071f8",
"16cc7a6b-d4c3-4cd1-9648-5247eb75ee2d",
"8150213d-5098-4e4a-a7f7-9afd7765c413",
"95880a25-18ee-4ffc-a122-54f186cfcc45",
"4921008f-2d1a-473b-baca-16c1a84f508a",
"0c935bc6-cc60-407a-a939-64fa5d8c4a74",
"9fbde9d0-7244-431f-97c5-eb33fdebd461",
"6c2cc331-962e-46c8-8d6b-591307a9c64c",
"d95d4d25-831a-4b4b-956b-ea9bafd3baed",
"b8c8a277-02c4-48ec-9351-6708fc36d289",
"b7a94bf3-5ba8-4480-b335-9d856bc75dcf",
"60385890-bcc1-48a1-82df-5dac4158ddfd",
"de66898b-aa93-4904-ad54-0499b1a863ba",
"be25917f-a8ac-4cb6-9506-09b3621eeb92",
"c2835b16-e705-4164-a312-14730393faab",
"f3005a29-2f39-4a93-a83c-6e0a4f81bfcc",
"b4a6c414-affc-4993-8709-ef57ad0c21ed",
"1a731034-8a34-40dc-8422-30173575c9e6",
"31c476b0-4798-4b5b-90c3-00b3f26f891a",
"9c2e72cf-74e7-446b-8b9f-9919602bee92",
"7f319e86-dcb4-4904-9315-2cc428dfd034",
"def9415a-06bc-4817-bcd2-05a442e67a81",
"215649a7-704a-4285-b97c-bd9d43f6fa5e",
"7d55545c-42e1-4a10-a680-0bfa74c54979",
"1aa6b929-58ee-49fa-ab1f-fed23dad3080",
"eb902def-2f50-43cf-ab9f-92361f41c2cb",
"3914c7d3-e744-4972-9718-aab45b188ec9",
"2312b302-5c3a-4ec8-beb3-aa1afc79a20d",
"fde1c29b-32f5-4d6c-a196-4a5aeee1bbfa",
"a48736a9-37af-4721-a69b-256fe0afe9ed",
"4e6b1059-9e76-4ec1-82e0-8e8af210f61a",
"c62b4667-2343-4e75-b149-52616c38e6dc",
"a60f8787-ee68-4041-a157-b8e12a02ab1d",
"11fdb91f-ac7c-47d5-b591-835e1b0aaa21",
"b7a7bc07-ec65-444e-83d2-d2f904a2752f",
"aeeb7441-44cd-4ffb-b36d-77e673fd44cb",
"712185b1-f379-42b0-b612-ef687ae8a93e",
"03aea060-ff0e-4620-8f6a-25ee4c9b9288",
"3d5e2181-4da2-4e90-a9e0-f4947a6a837b",
"d67a16f8-6719-49b3-9f42-c2c13477f604",
"cdf997a5-b381-4ad8-8649-56984cb6b4bf",
"a98425a3-2f94-44ba-910e-a2d9509ad2b5",
"389ec86a-84fc-4873-a597-8f7e50e576f4",
"0539695c-8533-4332-8f54-6ac6e27f8b51",
"d267b0bf-3198-4fc6-985f-dcf8a0019aa9",
"3bf50b45-fd81-4153-8048-a8ecba22fbed",
"f929de2c-660e-400a-9555-b26e6bcb08c9",
"bcf02899-24bf-4de3-92df-4827838ff04c",
"f2b40f9a-fb9b-4e22-ba18-c19d286708de",
"0a7b8575-872d-4c38-be54-bdbf15273d6c",
"27dd9934-62d3-4fdf-a1f8-3f78a872b445",
"3054cc98-79f6-4471-8bf1-dd15c16785b4",
"85bb38f4-5005-4501-a019-82fa2d00478b",
"01e83ed0-d2d9-482c-995d-f61012e6de8c",
"fe32063b-97bf-41c3-82f6-d9764c743ee6",
"38532fac-c9c8-4176-bcce-90ca780dff15",
"2cec6f7b-1027-4036-b2b6-7a8376e5188c",
"e0104f29-50d9-462e-abca-2248af40a1ff",
"4c4834cd-7e42-4fe8-a8c7-7363cc131419",
"4eb8da6c-0295-451e-b6fd-6f30174a4074",
"928c5312-17a2-4557-b523-d207cacc332b",
"fa5ed2fe-0d1f-4a7f-bcfb-6428b8cc7a20",
"a8b5bdcf-c257-4daf-ac6d-6e1dcfb6b5fc",
"4b962fc0-9b9b-4ae6-83c7-a91bff6234fc",
"d152c22a-3e32-4cab-80a4-ca793c7a615b",
"5a6c49ff-aedf-41bd-96b6-787087bd449e",
"a08d7253-ceb7-48e0-9173-0f08bfe1a7a5",
"90016233-b808-4a9a-88e9-77b8da19fc30",
"a39a0120-40ac-4403-906a-46ee99fb4460",
"1062e89e-dbfc-4950-8f63-f8fddbf5effa",
"9e5e3be8-6518-49f1-8692-4499291ba057",
"9ae54466-bac0-4e4a-a59f-7b6da328a466",
"11e84056-e811-44cd-aaea-95a81116cbd2",
"90640248-822d-4fd9-a79a-d14b645ef717",
"64fb30e3-c792-48ed-a1a8-65d7fc72676b",
"c54e261b-107b-4d8d-a5c7-d7e59884f548",
"e43ed4e9-7a46-4e74-8e4e-ce4c45e378f6",
"928fb67b-3620-488d-8d62-926678b6bb4e",
"071cc7dc-e180-4e7d-b9c7-27c9c8918db6",
"72b18791-b32a-44d2-8ddb-03b38341c4f5",
"5d1daa02-2004-416c-b826-4b82d6e8f55f",
"4bd74c21-1927-462f-838b-0829f470454c",
"0e9d46c8-8bf0-422b-8a39-5cbd1d0cbc6e",
"3f1ac600-192b-4cf7-82ab-71b0d642588e",
"8444a99d-f89b-424f-891d-e4a3a2e1011a",
"75715d94-9e99-4677-9d6c-835911445ef5",
"af7c949e-45e1-42e8-a84e-5017064520e3",
"488c6203-0e7b-48e6-bddf-fab1b000790d",
"4663237c-3363-4d10-bfdd-1d6e9c8f8c09",
"0d39b026-f0bb-4dc8-8ca2-2488af31a69b",
"3810b5b9-6234-415e-bbcc-9d7e7aa67d65",
"e2f7a168-451e-4e87-9bdd-17f1c665f813",
"ede8c72a-f44d-4e2c-bdd8-421a06bcd122",
"2599065a-ecc1-4858-91f2-973d6e0d0e74",
"773a4464-d49d-4a79-b187-80785c93ac96",
"4c401441-1c1d-4d35-865a-4638d0851ad4",
"c5c166cd-ae04-45cc-81bf-e8374be69c2e",
"c27985c5-afd3-4d5f-9125-7e8faf2d667b",
"187149db-1177-4011-a835-5b0237f8cfb7",
"8e8a0788-9490-4d36-8f39-df3fb0da2ace",
"8e22fea0-9b79-4fef-a858-6e93b63364d4",
"35117851-5aab-40ff-87cd-182a33d62cfb",
"b5963edb-2219-474d-9ffe-d876b13032dd",
"74b25533-ac51-45d1-aca5-9d3597107c6f",
"775fb804-28ce-4548-96d2-d2f90af93732",
"5fe9b5ca-dc7a-43e6-a32a-6fb0fd0c908a",
"484e4842-8568-4bea-bcff-8f4198bffe63",
"ab560ce7-a66c-4930-a29c-e7b5a828ebf0",
"0c2bb659-2765-46eb-9d39-ec886380d1d1",
"1a28c98e-14cf-4352-ad06-80761fbec000",
"54caba4a-26f8-4824-800f-ad9075650ecc",
"84549880-59e2-486a-834a-ccbe714c2de4",
"7d5f227b-2fb9-4392-86f3-33c7cd96ef41",
"2ecb908f-1e8c-49d7-85ec-6159d42afcc9",
"b23d57d7-32b5-4712-8796-02c14ac1b0b5",
"5182277d-cef1-464a-97b3-8c6403734ed5",
"f9658ec6-3f9c-4cad-a308-00cb5929b2c1",
"fa492526-112f-4db2-934a-fbf786e1711d",
"97cb5d85-c92c-4bcf-bbb9-e02d4ec1f875",
"c3b9d899-0e06-4eef-927d-ec7935c1ed4d",
"ce5ced9e-b21d-4863-9dba-d44ae4f0de68",
"015a5492-1277-4048-952a-b38dbe34824e",
"be1a0e79-bb9b-4b18-a7b8-b1e6b0b4f1ac",
"fc7407ae-9a6b-4a3a-b929-d5ea9ce8499f",
"308be146-8ced-46b9-a20f-47a88c3ef047",
"c6deba73-c85b-458d-9027-692dfae8326d",
"5d086250-cab1-485e-980f-0006bda68262",
"98e53bfe-bb37-40b0-acf6-94e5a72d6de7",
"05d2a892-a3b8-4055-b56d-438a4a599dfa",
"54c4f0b1-45cd-49f0-91fc-0d48bd7f0a5e",
"bd223248-af12-4ce9-9380-4f9a85be38db",
"a21224b5-5d2b-4a5b-bf42-1b885ba6738d",
"94032c42-8ed7-4bf1-bff6-952262dab315",
"7b7f786c-9544-42f0-aa9c-e442ae0cb89f",
"ca52f7b4-9541-4fa7-bdcd-ac8e9aa1d3e2",
"2304bbc8-679f-4c68-9861-88b7c579ff3d",
"5506f2f2-c5d1-4c6e-b0d5-77439ad30136",
"b5508d7c-b0dc-4a74-b66e-8daa457b2ca6",
"eef0db1f-bd4a-415f-9c14-722f563ef5f1",
"df765487-43f8-4e6c-8a43-a6efeb42abc7",
"985cb64a-33d2-44fe-b806-8aa848fdafdf",
"8c2c62d9-471c-4164-a88d-dfdaa139a4d6",
"ba5bb332-7307-4f58-8e3c-5e262ef7ded5",
"67c46151-7449-407a-a8b3-a283ba3f0771",
"1764aba8-641d-4eb8-ade5-ff33efafb054",
"fc88b2c7-4105-4295-9ba5-022213b4e9c7",
"ea39545e-f62d-4f6d-9b01-225928f8af6f",
"8603d0a9-0341-4939-8934-f4c655369cf6",
"35b71e3f-6f91-4f84-92d2-2a2a4868f795",
"086ad5ad-cfdb-4d5b-9fdd-4e231b14392e",
"b288586d-4b38-4f68-b55c-babc4ec1e58b",
"99804be7-bb4f-418e-8b0e-084920c60026",
"cfe44267-633b-42ec-b64b-2ac398d67996",
"c424e2e3-fea2-4a11-a1f8-0acb63e60ecd",
"e60d6991-27b9-4231-8aef-85bb120b780a",
"4f40bf02-bb7c-4a4c-9556-1f3a59dfca6c",
"f0f4ffbf-2802-4038-b202-7d6035237d76",
"a1982f4d-5724-4606-ab7e-c92ae12293f4",
"970f716b-262f-4962-a7e4-be9abb6d688e",
"09d07557-946b-4697-a8dd-5af6a3b5c6ce",
"9259ac5f-f59d-4d46-ad09-6196c948c549",
"b9d863ab-868a-40e7-8aec-3c2a7b9eff3e",
"98ff2e73-1088-4215-8ceb-32e530891352",
"da2a65b6-3048-4f88-a90c-0b3d8bda1b09",
"6b27f4bc-9b38-4fe3-8999-b53c12e44178",
"0ae33358-b7b1-4170-b542-2cfee31795e3",
"c8231a10-b37c-414a-a466-bd903b9c257d",
"add67d78-15a0-4444-957c-bd3210f30c29",
"d38e62dc-e839-4935-acb0-a63b15bbdda9",
"58a5a102-ff16-40db-b1cc-e64640aaf72e",
"906af8ef-eec5-489f-8718-742a3c148096",
"794248b0-9abe-4034-9f11-a594aeca3a1f",
"cd9e1162-7941-4b67-a6cd-bcd2a1b6629e",
"811df2a9-6c47-43bb-aa98-6d45a0ac0464",
"db09c273-56ae-4ef9-a5ed-53027aa7c63e",
"62ee6c6f-c71e-4935-bac3-c7e2a725467b",
"31e391dc-8eb4-4bd0-88ac-96b1499d0f56",
"f866b686-1234-4b74-82e6-055b27b82559",
"75d24dc5-6187-4d8d-8999-64b8b6b74328",
"ae23e9e6-2d06-4844-8fa3-77d88bbecb3c",
"67eacdce-905b-4d4b-a5ea-d6dbc64fcd59",
"648aad4c-990b-4411-b26a-bc1b22ea325d",
"e8e7f67d-09a3-4942-931c-b0edf36449c3",
"d1dd6c98-6f71-4dce-8c8c-014e53b42d53",
"cbd3dd08-a48a-409a-ac2d-32fa8cf9f07b",
"bca0ecb0-983e-44ae-b9cb-7d9aa62fdd72",
"e922ca14-9ad0-445e-af72-832e3cf41544",
"8ec08c75-5b14-4dd4-bd65-762cc722ccf6",
"a048f9e3-d664-486d-91fa-dd6d44c4844e",
"2a98983c-ce8d-4f22-925a-f2ae63d142e0",
"98ec79a7-ca3b-4e96-b55c-a903e4e6a941",
"bc67441f-c938-4edb-9e78-ae92985cb3cf",
"ac9af7b3-541d-4ec3-8ca3-2af8e7b7780f",
"c03ae242-1ab7-4e12-bfda-ccb643df69ca",
"81535548-ffdf-49a6-8e9f-555f9878434f",
"9865808e-d0d8-48a9-92e0-c266cce3a564",
"7ea103f7-5eae-4b6e-995c-e7cb195c25d6",
"114deb37-88f1-4bc7-8832-16a1e19c8319",
"32220f4d-dcfa-4b17-bdd6-0db8df1b5be7",
"5e7ae361-1c12-44b7-a239-95bbabc72f35",
"96cacc8f-4deb-4a59-ad28-4b88f48a8ae2",
"f7b38157-4c32-4626-84c1-d0ed00e1f791",
"2ab7740d-e4c2-4192-a441-958490c545e2",
"4bc071a1-b206-4a02-87e8-d4b5f02fd8dd",
"c7cc8367-1638-44c1-88f8-7c17aab7d1f9",
"235f7f92-2755-4e09-be32-c77907354817",
"3e7837c3-949d-4766-8941-6917129e9c68",
"71cc921e-e290-488c-a225-78519565fd30",
"b5ef1fd9-fa29-40ec-9613-22622f0b625d",
"407e703d-8992-4755-bac2-701df550e0ad",
"bfaf47d5-5a5e-40bb-885c-e399a766e093",
"ee8323e3-e919-4677-ac2b-93293ae9575d",
"944d5492-0edd-4567-a843-c89765651012",
"c8edbe68-1699-4f17-b44d-5655300f3586",
"d25c1e04-51e3-406f-a147-71a6b593f03b",
"39fc3789-a678-4b16-bef6-5ba110c5f173",
"ba01862b-19a2-447d-a727-f0714195cc22",
"10e32d25-149f-4393-8cf9-88b3e9d786e1",
"2fe485c6-b9d8-4f25-9274-a3c95f011171",
"79634317-905e-44fd-a7a0-00647c27ffc7",
"5491508a-4135-478d-8b07-2e5a0d3fc166",
"df6444b2-1613-4616-a292-58a4d95602a1",
"5dfdba63-19ef-4407-ad54-6fea0937ea1d",
"129f1884-4bf6-4a6d-b5c3-e9633d292c9c",
"3c1746db-7ed1-490e-8587-cdcc0214b6dc",
"aa8bb2a3-8f6f-44cc-8339-c838c975f29d",
"07a1fccf-6b4a-4503-8bf0-027e3e47bab6",
"a77cf63a-0469-4318-a996-b5b74beafadc",
"ba51fec6-8278-4b7d-a745-5421216bc1b6",
"adcf4720-2d79-4fb4-8734-2eef384f8f3f",
"d54292a5-530d-4b8d-a01c-f9c7e5ae4168",
"d65a1162-a653-4146-af19-dfbeec0a1801",
"4752f653-5d32-4a7e-922a-90e95bad90f6",
"6024a934-e649-482f-bd4f-04c38336dec6",
"63faec65-05a8-4bf3-86a2-27fe7b4532cd",
"feb947ec-018c-47f7-9b78-93f0c5421796",
"b42d227d-7b0e-40ad-adba-a551bc6eba82",
"4e708f21-ff20-404b-a4d2-f9e970e4b4ff",
"22c52644-3f89-44c5-847f-50fc0bbc1c74",
"3bac4106-ed2b-47aa-82e1-f3c1de3f67b5",
"110ec294-fbee-4b57-a71a-de7626adf91d",
"8dd768f8-84e2-441b-9f8f-b29dd58a1cfa",
"4943aafc-130a-4113-96da-3a81f7f7d003",
"c7e251ce-9ca5-441d-8099-41dd3aa7ca2c",
"3a78b415-0c84-4810-9235-1cc086cc1e3d",
"8a8a7e7d-c2a0-46c1-b8b4-501f25729896",
"aa099002-23be-404e-a778-5bbae9d6079f",
"d27103e2-31b4-4175-8b64-8183b29c9536",
"e7cf10b0-08fe-4e80-8e89-a7e9048bd41a",
"77cb587c-93fd-453a-b83a-c29c39637d4a",
"928b45cf-3223-4667-95b3-beea056a3b1f",
"e3d15d8f-8356-4254-a872-bc81521fea08",
"8f429ebe-eb64-459e-9248-3c4dbc387a81",
"14e84142-af7c-4b26-b923-15c3db4623cc",
"94671d00-b865-4978-957b-8cbbaa8dbaed",
"508708ea-358e-46ee-a71e-cc43102e9470",
"45e80461-acb9-44af-8544-bb8b34cd9045",
"9bab32fe-2b29-449d-b830-82edca8b44eb",
"2ea86b07-60b3-4af3-a715-e3f472436a17",
"4adbeccb-4e0c-40bb-b920-dfc95a024969",
"6f4b2304-e5aa-4ca9-904f-098288cb324e",
"e86d68e0-3e3b-4c2a-a3db-9235c9ed7955",
"6008a61c-a054-4261-8790-d66d0cd6392f",
"31a0535f-91af-437b-a192-96f9c93f6dbe",
"a8ec96e8-170c-4dc9-bd01-2a539bac458c",
"e239dcb2-12e1-4b8a-8bd3-02d2f957734b",
"4b32b2b9-3413-4849-b7f0-4ea2c5ca6132",
"5358993c-1644-4be1-a599-7384259fb736",
"851ed8b6-5620-47a3-adc7-5fb2df328b8c",
"e1c528b1-1077-473d-95dd-cee015d668d2",
"87fca4bf-468c-4117-873e-ef6e288ae004",
"0710191f-ca8e-4dc5-b601-105da15d9adb",
"9ed45dab-f8c4-4dba-9510-4d2356e54826",
"4a969e27-3c4a-485d-a53a-6e3f7a969e6e",
"455d1a0d-65cd-4521-92bc-053815eddc88",
"1556d497-cf58-49ca-acb4-0cbae115254f",
"c4a58190-398b-421e-9ffb-598c3459d732",
"f4b6b61d-8dec-4aa0-87e8-09292c0616fd",
"a29904ff-257e-455d-9e6c-dbc5af8cf3d2",
"f58ce176-794f-4dcc-9632-9fc6767baae3",
"c436c98a-95f6-4a14-a4ea-2b9fbcf29b07",
"95a9c874-c577-4c21-914d-18d5b17739a4",
"550b3c5d-9fec-41ee-82c3-892545460e43",
"b1d9999c-3b45-43dc-9df9-42259b5e9eb9",
"f356d11d-4614-4f52-8a28-31c14b1d040e",
"72cab1df-2867-4ce8-a6d3-d3f6920aa38e",
"5fd1b059-9156-4892-a34b-9ea100622a1b",
"480c0953-7f10-4988-a7be-f2dbb53ec847",
"7564dbad-6925-4f15-9ce0-640d6f6fe1e9",
"c80f38c8-726c-4d22-8ea4-42a5be2f098c",
"fcdf3464-491c-4cc0-a692-a8865a834f01",
"994afbdc-99ca-4037-97f8-59ebf01b76f2",
"fa924508-2ebe-49f8-9f6e-b62021933a5f",
"affdfb6e-47ec-4a7e-aa6f-b69d2dff939f",
"1460c446-6ba6-403e-82e0-56fc2b60fb0e",
"01593f26-c61e-42ad-9ac1-e8ab17f4bb08",
"a4392c3d-7021-4b5c-8b78-c045d1ddaec6",
"d2cbcf9f-3429-4129-b60b-4b2896b5894b",
"ab7862c5-f9c5-4515-889b-71f0a40beb20",
"bd3cdb86-c7c7-477c-bfd3-6ff165b0581d",
"5613df26-2b16-4e51-9dac-3f0f7ad09f9f",
"1612be45-a1fb-4629-9b30-9e5cbcca882b",
"f9f7ec5b-113b-4aa0-aa3c-7e90f711f485",
"c27e82f5-6a9e-43c3-ad02-678c06560c88",
"60b4e3af-7110-475c-83cd-dfff94d2f759",
"a2b30fe2-36ba-4904-b910-f0dd4b899e5d",
"2ab32dee-42f8-407d-8559-31be6337926c",
"162ad2d7-cf2d-4022-9e92-0980c1a1eecf",
"9e64cad6-2387-405e-9deb-067d970869b5",
"a8865afd-39b7-4beb-841d-e4263f54895c",
"64f4592c-a157-4aae-b8fb-74df43466db8",
"808f43f8-d71a-49b1-a0fd-9dae8f01ab9b",
"8d1d3224-53f9-4211-ac51-4308aa802adc",
"1467cb66-f19f-4f64-9b95-51bfc5ab4778",
"5b4de720-d042-47b3-824a-542b12e7c771",
"1522db04-f8e2-465d-8e7f-c43dd097caea",
"0f4bc3ad-3804-4127-8eaf-5a31cd4ce3fe",
"9c7beff1-f554-4bf7-a8ad-0c64fa2a5e5a",
"84011d90-3779-403c-af42-e8db8d73b245",
"d171226e-6774-4af9-9b67-3226b6e14464",
"bdf30298-a5d8-4edb-890d-6f6f997688ad",
"d954de3e-0c5c-447d-be48-2dca5b6940f2",
"5643826b-dbe0-4dfd-b0b4-833ddcbc427f",
"16a11b29-cf63-4301-90a8-8e0c9868d27c",
"da036545-5cfd-4069-bfd3-8eb4e16f72f1",
"8996eaa1-2c19-47f4-8833-f408b2a0196d",
"a3fbe2f2-1f7a-46a7-a67a-7c2da7d7af6e",
"0964bf51-f620-4a71-9ab3-ff631f2099bb",
"091ecde4-9cf5-4bc8-88f7-ed1700a7a08c",
"f5e17bed-fad0-4559-bc2d-c3bd989bd1f6",
"83938491-3c89-424e-b359-d9f21c9afd31",
"fa412491-8d03-412d-9f0a-1e8614b5b2f4",
"1cbdaf11-aac7-49ef-b7ee-6d5e1e2078a3",
"d3be07fa-bbb2-4f2d-915e-30adc36055ec",
"6e07235d-400e-42e5-a93d-cc52e9d6fb53"
);

$stack = array('a8865afd-39b7-4beb-841d-e4263f54895c');


while (count($stack) > 0)
{
	$id = array_pop($stack);
	
	//if (!have_already($id))
	if (!in_array($id, $done))
	{
		echo "-- Fetching node $id...\n";
	
		fetch_ala($id);	
		
		
	}
	
	$done[] = $id;


}

?>