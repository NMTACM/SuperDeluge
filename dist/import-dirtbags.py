#!/usr/bin/env python

import os
import sys
import hashlib
import shutil

import MySQLdb

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
    """This function yields puzzles.

    The puzzles are python dictionaries containing a `category` name,
    an integer point `value`, an optional `author` name, an optional
    `notes` field for private notes on how to solve a puzzle, a
    `description` shown to the user in Markdown format, an exact text
    `answer`, and `files`, which is a list of tuples of (filenames to
    show to the user, file locations). All of these files are copied
    into the files/ directory and named with their hash, and the
    filename to show to the user is shown on the link on the puzzle's
    page."""
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
                            puzzle['description'] = f.read().rstrip()
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


category_ids = dict()
def get_category_id(catname, c):
    if catname not in category_ids:
        c.execute("""SELECT id FROM category WHERE name = %s""", (catname,))
        catid = c.fetchone()
        if catid is None:
            c.execute("""INSERT INTO category (name, active) VALUES (%s, 1)""", (catname,))
            return get_category_id(catname, c)
        category_ids[catname] = catid[0]
    return category_ids[catname]

if __name__=="__main__":
    file_dest_dir = os.path.join(
        os.path.dirname(__file__), "www", "files",
        )
    if not os.path.isdir(file_dest_dir):
        os.mkdir(file_dest_dir)
    db = MySQLdb.connect(host="localhost", user="ctf", passwd="", db="ctf")
    c = db.cursor()
    for puzzle in get_puzzles(sys.argv[1]):
        catid = get_category_id(puzzle['category'], c)
        c.execute("""INSERT INTO puzzle (value, answer, description, notes, author, category_id) VALUES (%s, %s, %s, %s, %s, %s)""",
                  (puzzle['value'], puzzle['answer'], puzzle['description'],
                   puzzle['notes'], puzzle['author'], catid))
        print "Inserted puzzle into category", puzzle['category'], "with value", puzzle['value']
        puzzleid = None
        for puzzlefile in puzzle['files']:
            if puzzleid is None:
                c.execute("""SELECT id FROM puzzle WHERE value=%s AND answer=%s AND category_id=%s""",
                          (puzzle['value'], puzzle['answer'], catid))
                puzzleid = c.fetchone()[0]
            filename, oldfilepath = puzzlefile
            filehash = hashfile(oldfilepath)
            newfilepath = os.path.join(file_dest_dir, filehash)
            if not os.path.exists(newfilepath):
                shutil.copyfile(oldfilepath, newfilepath)
            newfileurl = "files/"+filehash
            c.execute("""INSERT INTO puzzle_file (puzzle_id, name, url) VALUES (%s, %s, %s)""",
                      (puzzleid, filename, newfileurl))
            print "Inserted puzzle file", filename
    c.close()
    db.commit()
