<?php

/*
 * libglocal
 *
 * Copyright (C) 2018 SOFe
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace SOFe\Libglocal\Parameter;

use SOFe\Libglocal\MessageParameter;
use SOFe\Libglocal\MessageParameterFactory;
use function strtolower;

class DefaultMessageParameterFactory implements MessageParameterFactory{
	public function createParameter(string $type, array $data) : ?MessageParameter{
		switch(strtolower($type)){
			case "string":
			case "text":
			case "num":
			case "number":
			case "numeric":
			case "int":
			case "integer":
			case "float":
			case "double":
			case "decimal":
				return new AsIsParameter();
			case "bool":
			case "boolean":
				return new EnumConversionParameter([
					[true, $data["true"] ?? "true"],
					[false, $data["false"] ?? "false"],
				]);

			case "quantity":
				return new QuantityParameter($data);

			case "enum":
			case "list":
			case "array":
				return new ListParameter($data["delimiter"] ?? ", ");
		}

		return null;
	}
}
