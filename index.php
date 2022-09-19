<?php
if (!isset($_GET["id"])) {
?>

<!doctype html>
<html>
  <head>
    <title>mc-uwp-dl</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
    
    * {
        font-family: Arial;
    }
    
    html, body {
        height: 100vh;
        margin: 0;
        padding: 0;
    }
    
    body {
        display: grid;
        justify-content: center;
        align-content: center;
    }
    
    input {
        font-size: 1.25rem;
    }
    
    .mainView {
        text-align: center;
        padding-bottom: 7.5rem;
    }
    
    </style>
  </head>
  <body>
    <div class="mainView">
        <form action="">
            <h1 class="titleHead">MC-UWP-DL</h1>
            <input type="text" name="id" value="UUID" onfocus="this.value=''" maxlength="36">
            <input type="submit" value="Download" placeholder="hint"><br>
            <p><a href="https://github.com/MCMrARM/mc-w10-versiondb/blob/master/versions.txt" target="_blank">Find UUIDs</a>&nbsp;&nbsp;&nbsp;<a href="https://github.com/StruckX/mc-uwp-dl" target="_blank">Source Code</a></p>
            Credits to <a href="https://gist.github.com/MCMrARM/d4fe4a8f38e8a12034c1c830b19989c2">MCMrARM</a> for the base code.
        </form>
    </div>
  </body>
</html>

<?php
    exit;
}
$uuid = $_GET["id"];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$blob = '<s:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:s="http://www.w3.org/2003/05/soap-envelope"><s:Header><a:Action s:mustUnderstand="1">http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/GetExtendedUpdateInfo2</a:Action><a:MessageID>urn:uuid:af2aea53-49b2-4af5-b6df-80f78143023b</a:MessageID><a:To s:mustUnderstand="1">https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx/secured</a:To><o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><Created>2019-02-24T22:00:42.71Z</Created><Expires>2019-02-24T22:05:42.71Z</Expires></Timestamp><wuws:WindowsUpdateTicketsToken wsu:id="ClientMSA" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:wuws="http://schemas.microsoft.com/msus/2014/10/WindowsUpdateAuthorization"><TicketType Name="AAD" Version="1.0" Policy="MBI_SSL"></TicketType></wuws:WindowsUpdateTicketsToken></o:Security></s:Header><s:Body><GetExtendedUpdateInfo2 xmlns="http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService"><updateIDs><UpdateIdentity>';
$blob .= '<UpdateID>' . $uuid . '</UpdateID>';
$blob .= '<RevisionNumber>1</RevisionNumber></UpdateIdentity></updateIDs><infoTypes><XmlUpdateFragmentType>FileUrl</XmlUpdateFragmentType><XmlUpdateFragmentType>FileDecryption</XmlUpdateFragmentType><XmlUpdateFragmentType>EsrpDecryptionInformation</XmlUpdateFragmentType><XmlUpdateFragmentType>PiecesHashUrl</XmlUpdateFragmentType><XmlUpdateFragmentType>BlockMapUrl</XmlUpdateFragmentType></infoTypes><deviceAttributes>E:BranchReadinessLevel=CBB&amp;DchuNvidiaGrfxExists=1&amp;ProcessorIdentifier=Intel64%20Family%206%20Model%2063%20Stepping%202&amp;CurrentBranch=rs4_release&amp;DataVer_RS5=1942&amp;FlightRing=Retail&amp;AttrDataVer=57&amp;InstallLanguage=en-US&amp;DchuAmdGrfxExists=1&amp;OSUILocale=en-US&amp;InstallationType=Client&amp;FlightingBranchName=&amp;Version_RS5=10&amp;UpgEx_RS5=Green&amp;GStatus_RS5=2&amp;OSSkuId=48&amp;App=WU&amp;InstallDate=1529700913&amp;ProcessorManufacturer=GenuineIntel&amp;AppVer=10.0.17134.471&amp;OSArchitecture=AMD64&amp;UpdateManagementGroup=2&amp;IsDeviceRetailDemo=0&amp;HidOverGattReg=C%3A%5CWINDOWS%5CSystem32%5CDriverStore%5CFileRepository%5Chidbthle.inf_amd64_467f181075371c89%5CMicrosoft.Bluetooth.Profiles.HidOverGatt.dll&amp;IsFlightingEnabled=0&amp;DchuIntelGrfxExists=1&amp;TelemetryLevel=1&amp;DefaultUserRegion=244&amp;DeferFeatureUpdatePeriodInDays=365&amp;Bios=Unknown&amp;WuClientVer=10.0.17134.471&amp;PausedFeatureStatus=1&amp;Steam=URL%3Asteam%20protocol&amp;Free=8to16&amp;OSVersion=10.0.17134.472&amp;DeviceFamily=Windows.Desktop</deviceAttributes></GetExtendedUpdateInfo2></s:Body></s:Envelope>';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx/secured");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $blob);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/soap+xml; charset=utf-8',
    'User-Agent: Windows-Update-Agent/10.0.10011.16384 Client-Protocol/1.81'
));
$output = curl_exec($ch);
curl_close($ch);

$xml = simplexml_load_string($output);
$resp = $xml->children('s', true)->Body->children()->GetExtendedUpdateInfo2Response->GetExtendedUpdateInfo2Result;
foreach ($resp as $result) {
    foreach ($result->FileLocations->FileLocation as $location) {
        if (strncmp($location->Url, "http://tlu.dl.delivery.mp.microsoft.com/", strlen("http://tlu.dl.delivery.mp.microsoft.com/")) == 0) {
            header('Location: ' . $location->Url);
            echo $location->Url;
            exit;
        }
    }
}
