{ pkgs ? import <nixpkgs> { } }:

pkgs.mkShell {
  buildInputs = with pkgs; [ php php81Packages.composer nodePackages.npm ];
}
