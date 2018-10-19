package io.github.sof3.libglocal.intellij

import com.intellij.lang.Language
import com.intellij.openapi.fileTypes.FileTypeConsumer
import com.intellij.openapi.fileTypes.FileTypeFactory
import com.intellij.openapi.fileTypes.LanguageFileType

/*
 * libglocal
 *
 * Copyright 2018 SOFe
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

class LibglocalFileTypeFactory : FileTypeFactory() {
	override fun createFileTypes(consumer: FileTypeConsumer) = consumer.consume(LibglocalFileType)
}

object LibglocalFileType : LanguageFileType(LibglocalLanguage) {
	override fun getName() = "libglocal"

	override fun getDescription() = "Libglocal translation file"

	override fun getIcon() = Icons.LIBGLOCAL.px16

	override fun getDefaultExtension() = "lang"
}

object LibglocalLanguage : Language("libglocal") {
	override fun getDisplayName() = "lLibglocal translation file"
}
