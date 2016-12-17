NOTA [ES].
----------------------------------------------------------
En general el usuario con el cual se corre el GPG debe tener permisos de
escritura sobre el keyring (*.gpg).

Para plataformas WINDOWS, esto equivale a decir que el usuario IWAM_/IUSR_
dependiendo de si corre como ISAPI/CGI debe tener permisos de modify sobre
la carpeta en donde estan los archivos que hacen parte del keyring.

Adicionalmente hay que asegurar que el usuario con el cual corre el IIS
pueda llamar al command, para poder llamar al gpg.exe

cacls cmd.exe /E /G MACHINE\IUSR_MACHINE:R
