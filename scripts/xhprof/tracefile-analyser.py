#!/usr/bin/python
# coding: utf-8

# TODO: figure out how much time was spent including/requiring AWPCP files
# TODO: figure out how much time was spent executing AWPCP functions

import sys

class TracefileAnalyser(object):

    def __init__(self):
        self.counter = 0
        self.functions = {}
        self.records_stack = {}
        self.functions_stack = []

    def parse(self, filename):
        tracefile = open(filename, 'r')

        version = tracefile.readline()
        format = tracefile.readline()

        headers = tracefile.readline()
        created = ' '.join(headers.split(' ')[2:]).strip().strip('][')

        functions = self.parse_functions(tracefile)

    def parse_functions(self, tracefile):
        for line in tracefile:
            function_data = self.parse_function_data(line)

    def parse_function_data(self, line):
        columns = line.split('\t')
        columns_count = len(columns)

        if len(columns[0]) == 0:
            return

        if columns_count >= 10:
            record = self.parse_entry_record(columns)
            self.process_entry_record(record)
        elif columns_count == 4:
            record = self.parse_return_value(columns)
        elif columns_count == 5:
            record = self.parse_exit_record(columns)
            self.process_exit_record(record)

    def parse_entry_record(self, columns):
        record =  {
            'level': int(columns[0]),
            'function_number': int(columns[1]),
            'record_type': int(columns[2]),
            'time': float(columns[3]),
            'memory': int(columns[4]),
            'function_name': columns[5],
            'function_type': columns[6],
            'included_file': columns[7],
            'filename': columns[8],
            'line_number': int(columns[9])
        }

        try:
            record['parameters_count'] = columns[10]
            record['parameters'] = columns[11:]
        except IndexError:
            pass

        return record

    def process_entry_record(self, record):
        entry_record = self.records_stack[record['level']] = {
            'function_name': record['function_name'],
            'time': record['time'],
            'memory': record['memory'],
            'nested_time': 0,
            'nested_memory': 0,
            'included_file': record['included_file'],
            'filename': record['filename']
        }

        if self.should_count_record(entry_record):
            self.counter = self.counter + 1

        self.functions_stack.append(record['function_name'])

    def should_count_record(self, record):
        # return True

        if record['function_name'].find('another-wordpress-classifieds-plugin' ) != -1:
            return True
        if record['function_name'].find('awpcp') != -1:
            return True
        if record['included_file'].find('another-wordpress-classifieds-plugin') != -1:
            return True
        if record['included_file'].find('premium-modules') != -1:
            return True
        if record['filename'].find('another-wordpress-classifieds-plugin') != -1:
            return True
        if record['filename'].find('premium-modules') != -1:
            return True

        return False

    def parse_return_value(self, columns):
        record =  {
            'level': int(columns[0]),
            'function_number': int(columns[1]),
            'record_type': columns[2],
            'return_value': columns[3]
        }

        return record

    def parse_exit_record(self, columns):
        record =  {
            'level': int(columns[0]),
            'function_number': int(columns[1]),
            'record_type': int(columns[2]),
            'time': float(columns[3]),
            'memory': int(columns[4])
        }

        return record

    def process_exit_record(self, record):
        try:
            entry_record = self.records_stack[record['level']]
        except KeyError as e:
            entry_record = None
            print e, record, self.records_stack

        if self.counter > 0:
            elapsed_time = record['time'] - entry_record['time']
            used_memory = record['memory'] - entry_record['memory']
        else:
            # print 'irrelevant record', entry_record
            elapsed_time = entry_record['nested_time']
            used_memory = entry_record['nested_memory']

        # Proposed algorithm modification to take into account the time spent in nested calls of the same function
        #
        # if parent function is the same (direct recursion)
        #   increase parent nested time and memory using current function's nested time and memory
        # else if the same function is part of the ancestors chain
        #   **reduce** next ancestor nested time and memory by an amount equal to current function's elapsed time and used memory
        #       eventually the closest ancestor's nested time and memory will be increased by an amount that already includes current function's values,
        #       cancelling the negative value we added. As a result time spent executing nested calls of the same function is considred Own Time of that
        #       function, instead of being masked as nested time (time spent in a nested call to a **different** function).
        # else if the same function is not part of the ancestors chain
        #   proceed normally

        if record['level'] > 1:
            parent_record = self.records_stack[record['level'] - 1]
            parent_record['nested_time'] = parent_record['nested_time'] + elapsed_time
            parent_record['nested_memory'] = parent_record['nested_memory'] + used_memory

        if self.should_count_record(entry_record):
            self.counter = max(0, self.counter - 1)

        self.functions_stack.pop()

        self.update_function_stats(entry_record['function_name'],
                                   elapsed_time,
                                   used_memory,
                                   entry_record['nested_time'],
                                   entry_record['nested_memory'])

    def update_function_stats(self, function_name, elapsed_time, used_memory, nested_time, nested_memory ):
        try:
            function = self.functions[function_name]
        except KeyError:
            function = self.functions[function_name] = {
                'call_count': 0,
                'time': 0,
                'memory': 0,
                'nested_time': 0,
                'nested_memory': 0
            }

        function['call_count'] = function['call_count'] + 1

        # print function_name, function, elapsed_time, used_memory, nested_time, nested_memory

        if function_name not in self.functions_stack:
            function['time'] = function['time'] + elapsed_time
            function['memory'] = function['memory'] + used_memory
            function['nested_time'] = function['nested_time'] + nested_time
            function['nested_memory'] = function['nested_memory'] + nested_memory
        # elif self.counter == 0:
        #     function['time'] = function['time'] + elapsed_time
        #     function['memory'] = function['memory'] + used_memory


if __name__ == '__main__':
    analyser = TracefileAnalyser()
    analyser.parse(sys.argv[1])

    for function_name, function in analyser.functions.items():
        print function_name, function
