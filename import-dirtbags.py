#!/usr/bin/env python

import os
import sys
import hashlib
import shutil

def hashfile(filepath):
    sha1 = hashlib.sha1()
    with open(filepath, 'rb') as f:
        while True:
            data = f.read(1024)
            if not data:
                break
            sha1.update(data)
    return sha1.hexdigest()

def get_puzzles(path):
    for category in os.listdir(path):
        catpath = os.path.join(path, category)
        if os.path.isdir(catpath):
            for value in os.listdir(catpath):
                valpath = os.path.join(catpath, value)
                if os.path.isdir(valpath):
                    olddir = os.getcwd()
                    os.chdir(valpath)

                    puzzle = dict()

                    puzzle['category'] = category

                    if os.path.exists("@points.txt"):
                        with file("@points.txt") as f:
                            puzzle['value'] = int(f.read())
                    else:
                        puzzle['value'] = int(value)

                    if os.path.exists("@author.txt"):
                        with file("@author.txt") as f:
                            puzzle['author'] = f.read().strip()
                    else:
                        puzzle['author'] = None

                    if os.path.exists("@summary.txt"):
                        with file("@summary.txt") as f:
                            puzzle['notes'] = f.read().strip()
                    else:
                        puzzle['notes'] = None

                    if os.path.exists("@index.mdwn"):
                        with file("@index.mdwn") as f:
                            puzzle['description'] = f.read().strip()
                    else:
                        puzzle['description'] = None

                    with file("@answer.txt") as f:
                        puzzle['answer'] = f.read().strip()

                    puzzle['files'] = list()
                    for filename in os.listdir("."):
                        if filename[0] == "@" or not os.path.isfile(filename):
                            continue
                        pfile = (filename, os.path.join(valpath, filename))
                        puzzle['files'].append(pfile)

                    os.chdir(olddir)
                    yield puzzle


if __name__=="__main__":
    file_dest_dir = os.path.join(
        os.path.dirname(__file__), "www", "files",
        )
    if not os.path.isdir(file_dest_dir):
        os.mkdir(file_dest_dir)
    for puzzle in get_puzzles(sys.argv[1]):
        for puzzlefile in puzzle['files']:
            filename, oldfilepath = puzzlefile
            filehash = hashfile(oldfilepath)
            newfilepath = os.path.join(file_dest_dir, filehash)
            if not os.path.exists(newfilepath):
                shutil.copyfile(oldfilepath, newfilepath)
            print puzzlefile


