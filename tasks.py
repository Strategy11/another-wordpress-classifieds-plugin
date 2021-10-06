# coding: utf-8

from datetime import datetime
from invoke import task

import glob
import os
import shutil

def yellow(str):
    return str

def red(str):
    return str

def package(c, hash, output, buildir='build', exclude=['*/premium-modules/*'], name='', subdir=''):
    """Build an installable ZIP package of the plugin"""

    date = datetime.fromtimestamp(float(output))
    year = date.strftime('%Y')
    month = date.strftime('%m')
    day = date.strftime('%d')

    # organize builds by year/month/day
    buildpath = os.path.join(buildir, year, month, day, date.strftime('%Y%m%d-%H%M'), hash)

    if not os.path.exists(buildpath):
        c.run('mkdir -p %s' % buildpath)

    excludes = ' '.join(['--exclude=%s' % x for x in exclude])

    dirpath = os.path.join(buildpath, 'source')
    filename = '{0}.zip'.format(name)
    filepath = os.path.join(buildpath, filename)

    if os.path.exists(filepath):
        os.remove(filepath)

    if not os.path.exists(dirpath):
        c.run('mkdir -p %s' % dirpath)
        c.run('git archive %s | tar -x -C %s' % (hash, dirpath))

    with c.cd(buildpath):
        target = os.path.join(dirpath, subdir)

        if len(glob.glob(target)) > 0:
            with c.cd(os.path.join('source', os.path.dirname(subdir))):
                c.run('zip -qr %s %s %s' % (excludes, filename, os.path.basename(subdir)))
            c.run('mv %s .' % os.path.join('source', os.path.dirname(subdir), filename))

        elif len(subdir) > 0:
            print(red("\nError: directory '%s' doesn't exists.\n" % target))

        else:
            c.run('zip -qr %s %s %s' % (excludes, filename, 'source'))

    c.run('find build/ -name .DS_Store -delete')

    c.run('find {0} -name .DS_Store -delete'.format(buildir))


@task
def build(c, tree=None):
    """Build installable ZIP packages for the main plugin and all its premium modules"""
    ref = get_ref(c, tree)
    commit = get_commit(c, ref)
    date   = get_date(c, ref)

    package_plugin(c, commit, date)

    package_module(c, 'restricted-categories', commit, date)
    package_module(c, 'attachments', commit, date)
    package_module(c, 'authorize.net', commit, date)
    package_module(c, 'buddypress-listings', commit, date)
    package_module(c, 'category-icons', commit, date)
    package_module(c, 'campaign-manager', commit, date)
    package_module(c, 'comments-ratings', commit, date)
    package_module(c, 'coupons', commit, date)
    package_module(c, 'extra-fields', commit, date)
    package_module(c, 'featured-ads', commit, date)
    package_module(c, 'fee-per-category', commit, date)
    package_module(c, 'mark-as-sold', commit, date)
    package_module(c, 'payfast', commit, date)
    package_module(c, 'paypal-pro', commit, date)
    package_module(c, 'region-control', commit, date)
    package_module(c, 'rss-module', commit, date)
    package_module(c, 'stripe', commit, date)
    package_module(c, 'subscriptions', commit, date)
    package_module(c, 'videos', commit, date)
    package_module(c, 'xml-sitemap', commit, date)
    package_module(c, 'zip-code-search', commit, date)

def get_ref(c, tree):
    return tree or c.run('git symbolic-ref HEAD 2>/dev/null | cut -d"/" -f 3-', hide='stdout').stdout.strip()

def get_commit(c, tree):
    return c.run("git log --abbrev-commit -n 1 --pretty=format:'%%h' %s" % tree, hide='stdout').stdout

def get_date(c, tree):
    return c.run("git log --abbrev-commit -n 1 --pretty=format:'%%at' %s" % tree, hide='stdout').stdout

def package_plugin(c, commit, date):
    name = ''

    package(c, commit, date, buildir='build/packages', exclude=[], name=name, subdir=name)

def package_module(c, name, commit, date):
    name = 'awpcp-{0}'.format(name)
    subdir = '../{0}'.format(name)

    package(c, commit, date, buildir='build/packages', exclude=[], name=name, subdir=subdir)

@task
def module(c, name, tree=None, buildpath='build/packages'):
    """Build an installable* ZIP package of an AWPCP module.
    """
    ref = get_ref(c, tree)

    package_module(c, name, get_commit(c, ref), get_date(c, ref))

@task
def plugin(c, tree=None):
    """Build an installable ZIP package of the plugin

    @param tree         Git branch
    """
    ref = get_ref(c, tree)

    package_plugin(c, get_commit(c, ref), get_date(c, ref))

@task
def clean(c):
    c.run('rm -rf build/packages/*')


@task
def i18n(c, ignore_metadata_modifications=True):
    """Build PO and POT files

    Get makepot.php from http://i18n.svn.wordpress.org/tools/trunk/. You have to checkout the entire repo.
    """

    plugins = {
        'another-wordpress-classifieds-plugin': {
            'directory': '',
            'languages': []#['en_US', 'es_ES', 'fr_FR', 'de_DE']
        },
        'awpcp-attachments': {
            'directory': '../awpcp-attachments',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-authorize.net': {
            'directory': '../awpcp-authorize.net',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-buddypress-listings': {
            'directory': '../awpcp-buddypress-listings',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-campaign-manager': {
            'directory': '../awpcp-campaign-manager',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-category-icons': {
            'directory': '../awpcp-category-icons',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-comments-ratings': {
            'directory': '../awpcp-comments-ratings',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-coupons': {
            'directory': '../awpcp-coupons',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-extra-fields': {
            'directory': '../awpcp-extra-fields',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-featured-ads': {
            'directory': '../awpcp-featured-ads',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-fee-per-category': {
            'directory': '../awpcp-fee-per-category',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-google-checkout': {
            'directory': '../awpcp-google-checkout',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-mark-as-sold': {
            'directory': '../awpcp-mark-as-sold',
            'languages': ['en_US', 'es_ES', 'fr_FR']
        },
        'awpcp-paypal-pro': {
            'directory': '../awpcp-paypal-pro',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-region-control': {
            'directory': '../awpcp-region-control',
            'languages': ['en_US', 'es_ES', 'ru_RU']
        },
        'awpcp-restricted-categories': {
            'directory': '../awpcp-restricted-categories',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-rss-module': {
            'directory': '../awpcp-rss-module',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-stripe': {
            'directory': '../awpcp-stripe',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-subscriptions': {
            'directory': '../awpcp-subscriptions',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-videos': {
            'directory': '../awpcp-videos',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-xml-sitemap': {
            'directory': '../awpcp-xml-sitemap',
            'languages': ['en_US', 'es_ES']
        },
        'awpcp-zip-code-search': {
            'directory': '../awpcp-zip-code-search',
            'languages': ['en_US', 'es_ES']
        }
    }

    for domain, config in plugins.items():
        plugin_directory = config['directory']
        languages_directory = os.path.join(plugin_directory, 'languages')

        if not os.path.exists(languages_directory):
            os.mkdir(languages_directory)

        pot_file = os.path.join(languages_directory, '{0}.pot'.format(domain))

        command = 'php -d error_reporting=E_ERROR ~/bin/wp-i18n-tools/makepot.php wp-plugin {0} {1}'
        command = command.format(plugin_directory, pot_file)

        print('Creating template from {0}.'.format(plugin_directory))
        c.run(command, hide='stdout')

        for locale in config['languages']:
            po_file = os.path.join(languages_directory, '{0}-{1}.po'.format(domain, locale))

            if os.path.exists(po_file):
                print('Merging existing message catalog {0} with template.'.format(po_file))
                c.run('msgmerge --sort-output -U {0} {1}'.format(po_file, pot_file), hide='both')
            else:
                print(yellow(c.run('msginit --no-translator -i {0} -o {1}'.format(pot_file, po_file), hide='stdout').stdout))

            print('Compiling {0}'.format(po_file))
            mo_file = os.path.join(languages_directory, '{0}-{1}.mo'.format(domain, locale))
            c.run('msgfmt -o {0} {1}'.format(mo_file, po_file), hide='stdout')

        print('')

    if ignore_metadata_modifications:
        c.run('source bin/remove-irrelevant-translations-modifications.sh')

@task
def compile_po_files(c):
    c.run('for i in $(git status --porcelain | sed s/^...// | grep \'.po$\'); do msgfmt $i -o `dirname $i`/`basename $i .po`.mo; done')
