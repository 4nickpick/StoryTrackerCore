<?php
/**
 * Case use of gnuPG class.
 *
 * @author    Enrique Garcia Molina <egarcia@egm.as>
 * @copyright (c) 2004-2005 EGM :: Ingenieria sin fronteras
 * @since     Viernes, Enero 30, 2004
 * @license   GNU LGPL (http://www.gnu.org/copyleft/lesser.html)
 * @version   $Id: gnuPG_example.php,v 1.0.7 2005-04-24 07:18:00-05 egarcia Exp $
 */

require('gnuPG_class.inc');

// create the instance, giving the program path and home directory
$gpg = new gnuPG('C:\gnupg\gpg', 'C:\gnupg');

// get the keys in the keyring
$Keys = $gpg->ListKeys('public');
if (is_array($Keys)) {
	// show all the keys
	print_r($Keys);
	
	// export the first key in the keyring
	$PublicBlock = $gpg->Export($Keys[0]['KeyID']);
	if ($PublicBlock) {
		// show the key exported
		echo "The KEY BLOCK for {$Keys[0]['UserID']} is:\n\n";
		echo $PublicBlock . "\n";
	} else
		echo $gpg->error . "\n";
} else
	echo $gpg->error . "\n";

// import a dummy key
$PublicBlock = "-----BEGIN PGP PUBLIC KEY BLOCK-----
Version: GnuPG v1.2.4 (MingW32)

mQGiBEDkScoRBACQtBkECbyk7zujxRTnGGh0QtdM3lxRTU5gkCf20PT6mMCnY/9t
nuCd1+eD7YEeu5hi/PAkhDr2mLSm1k3HnDofHMSK7YXL8wbiAS0+31YCoBQfKpvs
i+PDxglOkey243zTOxdqHUV7JZXouxeFnwGH7to01wCNmOyOx/fYQ3NrzwCgxsvU
Om15U9tsHvhy4jhFCE7x11ED/iua9KxoHIWFFKwEt61PW91cqpwJfwuHXKSN154R
rvt5UAp0HI95J1iHA1VrKaOPpAn579PdQVlgHoDRZJquTnKFrV/lzRdxTXwEUJtC
8NJAWL/xSMtK4LoGzFIz4fO11HPE0Zuo+BNIE3DdZ0/XwTjM+EG+lExFHFlhRAUo
TpTyA/48aPgK0V8tuCL1kpUF5JHIdlynJA6fcJUKSN3D9T/siw4UEIxVBKWGtFN1
8KwNVrCWB2Q9IinUyyVhVGo4dlOg1ubqHuD7p+IoWTKVpnKN5HBsOXetx6ecsXVl
D4esOk4IhSZ0ffaYbt79x/SCMwF4CfJaJmTkIW2L1Gy6AGb5XrQwVGVzdCBLZXkg
KFRoaXMgaXMgYSBkdW1teSBrZXkpIDx1c2VyQGRvbWFpbi5jb20+iFsEExECABsF
AkDkScoGCwkIBwMCAxUCAwMWAgECHgECF4AACgkQZAhJLLeuBVSYYQCdHPB2qIup
+U8HwRvBPHgznNahRYsAmwdrxPsL8KKm99sksSMtkKzKqNwAuQENBEDkScsQBACC
kbHg0svEJPChJCV1+EZFbBeL6Y+wBD3HBHK0jKjzRkdF1slcWa2y29yL0/4cdo+4
jXxdLWCiSR2RvFr0ti8LqlzLvApKPtlOhVB81eDewFxPanpTAaVYfDn+Rm54kixI
3mu3XEkT9yfWgLUgbQugiboT+HjfiLke3cOant2OnwADBQP+PbSkU+37jpK80EGM
OQ0mLFIU0xzJRccaHqflR9pwfaapC+CeMlJmqt4ut3Gfi+U+VoceZzdOv/GQm3vJ
rzYcHkHzQdDW4CDMlyMh9HXg1udShY8QNEBXwWpr+FdkNd4loOOwOqG1P8iQGDRi
LIZs/wx6oNCu7qDFdNAwVfJyNV6IRgQYEQIABgUCQORJywAKCRBkCEkst64FVGzT
AJ9xbE3mbE8fTdXAjBSya8aTsvSolwCffsjbB28c+M2epSVaZtVA/qRwdrs=
=BfWQ
-----END PGP PUBLIC KEY BLOCK-----";
$Imported_Keys = $gpg->Import($PublicBlock);
if ($Imported_Keys) {
	// show each of the imported keys
	foreach($Imported_Keys as $KeyID) {
		echo "The key {$KeyID['KeyID']}, {$KeyID['UserID']} was sucessfuly imported!!!\n";
	}
} else
	echo $gpg->error . "\n";

// generate a new key with out expiration
$GeneratedKey = $gpg->GenKey('Test Key', 'This is a dummy key', 'user@domain.com', '123456');
if ($GeneratedKey) {
	if ($GeneratedKey === true) {
		// get the all keys
		$Keys = $gpg->ListKeys('public');
		
		// the last key is the generated key
		$i = count($Keys) - 1;
		$GeneratedKey = $Keys[$i]['KeyID'];
		echo "The new key was created with KeyID = $GeneratedKey!!!\n";
	} else
		echo "The new key was created with Fingerprint = $GeneratedKey!!!\n";
	
	// use the last key to sign the imported keys
	if ($Imported_Keys) {
		foreach($Imported_Keys as $KeyID) {
			if ($gpg->SignKey($GeneratedKey, '123456', $KeyID['KeyID'], 3))
				echo "The Key {$KeyID['KeyID']} was sucessfuly signed by $GeneratedKey!!!\n";
			else
				echo $gpg->error . "\n";
		}
	}
	
	// use the last key to encrypt
	$PublicBlock = $gpg->Encrypt($GeneratedKey, '123456', $GeneratedKey, 'This is a success test!!!');
	if ($PublicBlock) {
		// show the encrypted text
		echo "The ENCRYPTED TEXT BLOCK is:\n\n";
		echo $PublicBlock . "\n";
		
		// now shows the decrypted text
		$PublicBlock = $gpg->Decrypt($GeneratedKey, '123456', $PublicBlock);
		if ($PublicBlock) {
			echo "The DECRYPTED TEXT is:\n\n";
			echo $PublicBlock . "\n";
		} else
			echo $gpg->error . "\n";
	} else
		echo $gpg->error . "\n";
} else
	echo $gpg->error . "\n";

//
// CLEAN PROCESS
//
// now delete the imported keys
if ($Imported_Keys) {
	foreach($Imported_Keys as $KeyID) {
		if ($gpg->DeleteKey($KeyID['KeyID']) === true)
			echo "The imported key {$KeyID['KeyID']} was deleted from the keyring\n";
		else
			echo $gpg->error . "\n";
	}
}

// now delete the generated key
if ($GeneratedKey) {
	if ($gpg->DeleteKey($GeneratedKey, 'secret') === true) {
		echo "The secret key for $GeneratedKey was deleted from the keyring\n";
		if ($gpg->DeleteKey($GeneratedKey) === true)
			echo "The public key for $GeneratedKey was deleted from the keyring\n";
		else
			echo $gpg->error . "\n";
	} else
		echo $gpg->error . "\n";
}

?>